<?php
namespace Rindow\RL\Gym\Core\Spaces;

use InvalidArgumentException;
use RuntimeException;
use Traversable;
use Interop\Polite\Math\Matrix\NDArray;
use Interop\Polite\AI\RL\Spaces\Space;
use Interop\Polite\AI\RL\Spaces\Dict as DictInterface;

/*
    A dictionary of :class:`Space` instances.

    Elements of this space are (ordered) dictionaries of elements from the constituent spaces.

    Example:
        >>> from gymnasium.spaces import Dict, Box, Discrete
        >>> observation_space = Dict({"position": Box(-1, 1, shape=(2,)), "color": Discrete(3)}, seed=42)
        >>> observation_space.sample()
        {'color': np.int64(0), 'position': array([-0.3991573 ,  0.21649833], dtype=float32)}

        With a nested dict:

        >>> from gymnasium.spaces import Box, Dict, Discrete, MultiBinary, MultiDiscrete
        >>> Dict(  # doctest: +SKIP
        ...     {
        ...         "ext_controller": MultiDiscrete([5, 2, 2]),
        ...         "inner_state": Dict(
        ...             {
        ...                 "charge": Discrete(100),
        ...                 "system_checks": MultiBinary(10),
        ...                 "job_status": Dict(
        ...                     {
        ...                         "task": Discrete(5),
        ...                         "progress": Box(low=0, high=100, shape=()),
        ...                     }
        ...                 ),
        ...             }
        ...         ),
        ...     }
        ... )

    It can be convenient to use :class:`Dict` spaces if you want to make complex observations or actions more human-readable.
    Usually, it will not be possible to use elements of this space directly in learning code. However, you can easily
    convert :class:`Dict` observations to flat arrays by using a :class:`gymnasium.wrappers.FlattenObservation` wrapper.
    Similar wrappers can be implemented to deal with :class:`Dict` actions.
*/

class Dict extends AbstractSpace implements  DictInterface
{
    protected array $spaces = [];

    /**
     * Constructor of :class:`Dict` space.
     *
     *   This space can be instantiated in one of two ways: Either you pass a dictionary
     *   of spaces to :meth:`__init__` via the ``spaces`` argument, or you pass the spaces as separate
     *   keyword arguments (where you will need to avoid the keys ``spaces`` and ``seed``)
     *
     *   Args:
     *       spaces: A dictionary of spaces. This specifies the structure of the :class:`Dict` space
     *       seed: Optionally, you can use this argument to seed the RNGs of the spaces that make up the :class:`Dict` space.
     *       **spaces_kwargs: If ``spaces`` is ``None``, you need to pass the constituent spaces as keyword arguments, as described above.
     */
    public function __construct(
        object $la,
        array $spaces,
    )
    {
        parent::__construct($la,
            shape:[],
            dtype:NDArray::bool,
            seed:null,
        );

        foreach($spaces as $key => $space) {
            $this->assertKey($key);
            $this->assertSpace($space);
            $this->spaces[$key] = $space;
        }

    }

    /**
     * @return array<int> $shape
     */
    public function shape() : array
    {
        throw new InvalidArgumentException("Unsupported operation to Dict space.");
    }

    public function dtype() : int
    {
        throw new InvalidArgumentException("Unsupported operation to Dict space.");
    }

    protected function assertKey(mixed $key) : void
    {
        if(!is_string($key)) {
            throw new InvalidArgumentException("The key of Dict space must be string");
        }
    }

    protected function assertSpace(mixed $space) : void
    {
        if(!($space instanceof Space)) {
            throw new InvalidArgumentException("The value of Dict space must be Space");
        }
    }

    /**
     * Return boolean specifying if x is a valid member of this space.
     */
    public function contains(NDArray|array $x, ?bool $throw=null, ?string $type=null) : bool
    {
        $type ??= '';
        if(!is_array($x)) {
            $valuetype = gettype($x);
            throw new InvalidArgumentException("type of $type must be array. $valuetype given.");
        }
        $xKeys = array_keys($x);
        $spaceKeys = array_keys($this->spaces);
        if($xKeys!==$spaceKeys) {
            if($throw) {
                throw new RuntimeException("Keys of the $type must be (".implode(',',$spaceKeys)."). (".implode(',',$xKeys).") given.");
            }
            return false;
        }
        foreach($this->spaces as $key => $space) {
            if(!$space->contains($x[$key],throw:$throw,type:$type.'.'.$key)) {
                return false;
            }
        }
        return true;
    }

    public function sample() : NDArray|array
    {
        $samples = [];
        foreach($this->spaces as $key => $space) {
            $samples[$key] = $space->sample();
        }
        return $samples;
    }

    public function offsetExists(mixed $key) : bool
    {
        $this->assertKey($key);
        return array_key_exists($key, $this->spaces);
    }
    
    public function offsetGet(mixed $key) : mixed
    {
        $this->assertKey($key);
        return $this->spaces[$key];
    }

    public function offsetSet(mixed $key, mixed $space) : void
    {
        $this->assertKey($key);
        $this->assertSpace($space);
        $this->spaces[$key] = $space;
    }

    public function offsetUnset(mixed $key) : void
    {
        $this->assertKey($key);
        unset($this->spaces[$key]);
    }

    public function count() : int
    {
        return count($this->spaces);
    }

    public function getIterator() : Traversable
    {
        foreach($this->spaces as $key => $space) {
            yield $key => $space;
        }
    }
}