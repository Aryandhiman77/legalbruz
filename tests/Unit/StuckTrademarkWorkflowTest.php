<?php

namespace Tests\Unit;

use App\Support\StuckTrademarkWorkflow;
use PHPUnit\Framework\TestCase;

class StuckTrademarkWorkflowTest extends TestCase
{
    public function test_terminal_timestamps_take_precedence_over_stale_status_values(): void
    {
        $this->assertSame(
            StuckTrademarkWorkflow::CLOSED,
            StuckTrademarkWorkflow::effectiveStatus(
                StuckTrademarkWorkflow::MONITORING,
                '2026-05-22 13:05:26',
                '2026-05-22 13:57:55',
                '2026-05-22 13:57:40',
            ),
        );

        $this->assertSame(
            StuckTrademarkWorkflow::RESOLVED,
            StuckTrademarkWorkflow::effectiveStatus(
                StuckTrademarkWorkflow::MONITORING,
                '2026-05-22 13:05:26',
                '2026-05-22 13:57:55',
            ),
        );
    }

    public function test_completed_execution_cannot_display_as_an_earlier_stage(): void
    {
        $this->assertSame(
            StuckTrademarkWorkflow::MONITORING,
            StuckTrademarkWorkflow::effectiveStatus(
                StuckTrademarkWorkflow::EXECUTION_ACTIVE,
                '2026-05-22 13:05:26',
            ),
        );

        $this->assertSame(
            StuckTrademarkWorkflow::AUDIT_IN_PROGRESS,
            StuckTrademarkWorkflow::effectiveStatus(StuckTrademarkWorkflow::AUDIT_IN_PROGRESS),
        );
    }
}
