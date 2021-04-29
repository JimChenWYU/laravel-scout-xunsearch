<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch;

use Closure;
use Throwable;

class Pipeline
{
    /**
     * The object being passed through the pipeline.
     *
     * @var mixed
     */
    protected $passable;

    /**
     * The array of class pipes.
     *
     * @var array
     */
    protected $pipes = [];

    /**
     * The method to call on each pipe.
     *
     * @var string
     */
    protected $method = 'handle';

    /**
     * Set the object being sent through the pipeline.
     *
     * @param  mixed  $passable
     * @return $this
     */
    public function send($passable)
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * Set the array of pipes.
     *
     * @param  string[]|callable[]|object[]|string|object|Closure $pipes
     * @return $this
     */
    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }

    public function then(Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes()),
            $this->carry(),
            $this->prepareDestination($destination)
        );

        return $pipeline($this->passable);
    }

    /**
     * Get the array of configured pipes.
     *
     * @return array
     */
    protected function pipes()
    {
        return $this->pipes;
    }

    /**
     * Get the final piece of the Closure onion.
     *
     * @param  \Closure  $destination
     * @return \Closure
     */
    protected function prepareDestination(Closure $destination)
    {
        return function ($passable) use ($destination) {
            try {
                return $destination($passable);
            } catch (Throwable $e) {
                return $this->handleException($passable, $e);
            }
        };
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return \Closure
     */
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try {
                    if (is_callable($passable)) {
                        return $pipe($passable, $stack);
                    }
                    if (! is_object($pipe)) {
                        [$className, $parameters] = $this->parsePipeString($pipe);

                        $pipe = new $className();

                        $parameters = array_merge([$passable, $stack], $parameters);
                    } else {
                        $parameters = [$passable, $stack];
                    }

                    return method_exists($pipe, $this->method)
                        ? call_user_func_array([$pipe, $this->method], $parameters)
                        : $pipe(...$parameters);
                } catch (Throwable $e) {
                    return $this->handleException($passable, $e);
                }
            };
        };
    }

    /**
     * Parse full pipe string to get name and parameters.
     *
     * @param  string $pipe
     * @return array
     */
    protected function parsePipeString($pipe)
    {
        [$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * Handle the given exception.
     *
     * @param  mixed  $passable
     * @param  \Throwable  $e
     * @return mixed
     *
     * @throws \Throwable
     */
    protected function handleException($passable, Throwable $e)
    {
        throw $e;
    }
}
