<?php

namespace MalikAd778\BladeAlly\Violations;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class ViolationCollection implements Countable, IteratorAggregate
{
    private array $violations = [];

    public function add(Violation $violation): void
    {
        $this->violations[] = $violation;
    }

    public function count(): int
    {
        return count($this->violations);
    }

    public function countBySeverity(string $severity): int
    {
        return count(array_filter($this->violations, fn(Violation $v) => $v->severity === $severity));
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->violations);
    }

    public function all(): array
    {
        return $this->violations;
    }

    public function filterBySeverity(string $severity): self
    {
        $new = new self();
        foreach ($this->violations as $v) {
            if ($v->severity === $severity) {
                $new->add($v);
            }
        }
        return $new;
    }

    public function filterByRule(string $ruleId): self
    {
        $new = new self();
        foreach ($this->violations as $v) {
            if ($v->ruleId === $ruleId) {
                $new->add($v);
            }
        }
        return $new;
    }

    public function filterByFile(string $file): self
    {
        $new = new self();
        foreach ($this->violations as $v) {
            if ($v->filePath === $file) {
                $new->add($v);
            }
        }
        return $new;
    }

    public function groupByFile(): array
    {
        $groups = [];
        foreach ($this->violations as $v) {
            $groups[$v->filePath][] = $v;
        }
        return $groups;
    }

    public function groupByRule(): array
    {
        $groups = [];
        foreach ($this->violations as $v) {
            $groups[$v->ruleId][] = $v;
        }
        return $groups;
    }

    public function sortByFile(): self
    {
        $sorted = $this->violations;
        usort($sorted, fn($a, $b) => strcmp($a->filePath, $b->filePath) ?: $a->line <=> $b->line);
        $new = new self();
        foreach ($sorted as $v) {
            $new->add($v);
        }
        return $new;
    }

    public function toArray(): array
    {
        return array_map(fn(Violation $v) => $v->toArray(), $this->violations);
    }
}
