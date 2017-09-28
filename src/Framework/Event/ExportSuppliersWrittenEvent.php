<?php declare(strict_types=1);

namespace Shopware\Framework\Event;

use Shopware\Context\Struct\TranslationContext;

class ExportSuppliersWrittenEvent extends NestedEvent
{
    const NAME = 'export_suppliers.written';

    /**
     * @var string[]
     */
    protected $exportSuppliersUuids;

    /**
     * @var NestedEventCollection
     */
    protected $events;

    /**
     * @var array
     */
    protected $errors;

    /**
     * @var TranslationContext
     */
    protected $context;

    public function __construct(array $exportSuppliersUuids, TranslationContext $context, array $errors = [])
    {
        $this->exportSuppliersUuids = $exportSuppliersUuids;
        $this->events = new NestedEventCollection();
        $this->context = $context;
        $this->errors = $errors;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getContext(): TranslationContext
    {
        return $this->context;
    }

    /**
     * @return string[]
     */
    public function getExportSuppliersUuids(): array
    {
        return $this->exportSuppliersUuids;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function addEvent(NestedEvent $event): void
    {
        $this->events->add($event);
    }

    public function getEvents(): NestedEventCollection
    {
        return $this->events;
    }
}