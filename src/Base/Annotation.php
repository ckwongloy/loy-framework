<?php

declare(strict_types=1);

namespace Loy\Framework\Base;

use Closure;
use ReflectionClass;
use ReflectionException;
use Loy\Framework\Base\Reflector;
use Loy\Framework\Base\Exception\InvalidAnnotationDirException;
use Loy\Framework\Base\Exception\InvalidAnnotationNamespaceException;

class Annotation
{
    private $regex = '#@([a-zA-z]+)\((.*)\)#';

    public function parseClassDirs(array $dirs, Closure $callback = null, string $origin = null)
    {
        foreach ($dirs as $dir) {
            if ((! is_string($dir)) || (! is_dir($dir))) {
                throw new InvalidAnnotationDirException(stringify($dir));
            }

            $this->parseClassDir($dir, $callback, $origin);
        }
    }

    public function parseClassDir(string $dir, Closure $callback = null, string $origin = null)
    {
        $result = [];

        walk_dir($dir, function ($path) use ($callback, $origin, &$result) {
            $realpath = $path->getRealpath();
            if ($path->isFile() && ('php' === $path->getExtension())) {
                $result[$realpath] = $this->parseClassFile($realpath, $callback, $origin);
                return;
            }
            if ($path->isDir()) {
                $this->parseClassDir($realpath, $callback, $origin);
                return;
            }
        });

        return $result;
    }

    public function parseClassFile(string $path, Closure $callback = null, string $origin = null)
    {
        $ns = get_namespace_of_file($path, true);
        if (! class_exists($ns)) {
            throw new InvalidAnnotationNamespaceException($ns);
        }

        $annotations = $this->parseNamespace($ns, $origin);

        if ($callback) {
            $callback($annotations);
        }

        return $annotations;
    }

    public function parseNamespace(string $namespace, string $origin = null) : array
    {
        try {
            $reflector = new ReflectionClass($namespace);
        } catch (ReflectionException $e) {
            return [];
        }

        $classDocComment = $reflector->getDocComment();
        $ofClass = [];
        if (false !== $classDocComment) {
            $ofClass['doc'] = $this->parseComment($classDocComment, $origin);
        }
        $ofClass['namespace'] = $namespace;
        $ofProperties = $this->parseProperties($reflector->getProperties(), $namespace, $origin);
        $ofMethods    = $this->parseMethods($reflector->getMethods(), $namespace, $origin);

        return [
            $ofClass,
            $ofProperties,
            $ofMethods,
        ];
    }

    public function parseProperties(array $properties, string $namespace, string $origin = null) : array
    {
        if (! $properties) {
            return [];
        }

        $res = [];
        foreach ($properties as $property) {
            list($type, $_res) = Reflector::formatClassProperty($property, $namespace);
            if ($_res === false) {
                continue;
            }
            $comment = (string) ($_res['doc'] ?? '');
            $_res['doc'] = $this->parseComment($comment, $origin);

            $res[$type][$property->name] = $_res;
        }

        return $res;
    }

    public function parseMethods(array $methods, string $namespace, string $origin = null) : array
    {
        if (! $methods) {
            return [];
        }

        $res = [];
        foreach ($methods as $method) {
            list($type, $_res) = Reflector::formatClassMethod($method, $namespace);
            if ($_res === false) {
                continue;
            }
            $comment = (string) ($_res['doc'] ?? '');
            $_res['doc'] = $this->parseComment($comment, $origin);
            $res[$type][$method->name] = $_res;
        }

        return $res;
    }

    public function parseComment(string $comment, string $origin = null) : array
    {
        if (! $comment) {
            return [];
        }

        $res = [];
        $arr = explode(PHP_EOL, $comment);
        foreach ($arr as $line) {
            $matches = [];
            if (1 !== preg_match($this->regex, $line, $matches)) {
                continue;
            }
            $key = $matches[1] ?? false;
            $val = $matches[2] ?? null;
            if ((! $key) || (is_null($val))) {
                continue;
            }
            if (! is_null($origin)) {
                $callback = 'filterAnnotation'.ucfirst(strtolower($key));
                if (method_exists($origin, $callback)) {
                    $val = call_user_func_array([$origin, $callback], [$val]);
                    // $val = is_object($origin) ? $origin->{$callback}($val) : $origin::$callback($val);
                }
            }

            $res[strtoupper($key)] = $val;
        }

        return $res;
    }

    /**
     * Setter for regex
     *
     * @param string $regex
     * @return Annotation
     */
    public function setRegex(string $regex)
    {
        $this->regex = $regex;
    
        return $this;
    }
}
