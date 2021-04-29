<?php declare(strict_types=1);

namespace JimChen\LaravelScout\XunSearch;

use Closure;
use JimChen\LaravelScout\XunSearch\Tokenizers\Contracts\MiddlewareContract;
use Throwable;

class TokenizerPipeline
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
     * @var string[]|callable[]
     */
    protected $pipes = [];

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
     * @param  string[]|callable[] $pipes
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
     * @return string[]|callable[]
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
                    if (is_object($pipe) && $pipe instanceof MiddlewareContract) {
                        return $pipe->handle($passable, $stack);
                    }

                    if (is_string($pipe) && is_subclass_of($pipe, MiddlewareContract::class)) {
                        [$name, $parameters] = $this->parsePipeString($pipe);
                        /** @var MiddlewareContract $pipe */
                        $pipe = new $name(...$parameters);
                        return $pipe->handle($passable, $stack);
                    }

                    return $pipe($passable, $stack);
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
