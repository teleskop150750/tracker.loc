<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\CreateTask;

use App\Exceptions\HttpException;
use Modules\Shared\Application\Command\CommandInterface;

class CreateTaskCommand implements CommandInterface
{
    /**
     * @param string[] $folders
     * @param string[] $executors
     * @param string[] $affects
     * @param string[] $depends
     *
     * @throws HttpException
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly array $folders,
        public readonly string $status,
        public readonly string $importance,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $description,
        public readonly array $executors = [],
        public readonly array $depends = [],
        public readonly array $affects = [],
    ) {
        $this->checkAffects();
        $this->checkDepends();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws HttpException
     */
    public static function createFromArray(array $data): static
    {
        return new static(
            $data['id'],
            $data['name'],
            $data['folders'],
            $data['status'],
            $data['importance'],
            $data['startDate'],
            $data['endDate'],
            $data['description'] ?? '',
            $data['executors'] ?? [],
            $data['depends'] ?? [],
            $data['affects'] ?? [],
        );
    }

    /**
     * @throws HttpException
     */
    private function checkAffects(): void
    {
        foreach ($this->affects as $affect) {
            if (\in_array($affect, $this->depends, true)) {
                throw new HttpException('Дублирование', 422, 422, ['affect' => ['Дублирование']]);
            }
        }
    }

    /**
     * @throws HttpException
     */
    private function checkDepends(): void
    {
        foreach ($this->depends as $depend) {
            if (\in_array($depend, $this->affects, true)) {
                throw new HttpException('Дублирование', 422, 422, ['depend' => ['Дублирование']]);
            }
        }
    }
}
