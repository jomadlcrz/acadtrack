<?php

declare(strict_types=1);

namespace App\Services;

class EvaluationService
{
    public function calculateEvaluation(array $grades, array $gradingPeriodWeights = []): array
    {
        if (empty($grades)) {
            return ['average' => 0, 'status' => 'No grades'];
        }

        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($grades as $period => $grade) {
            $weight = $gradingPeriodWeights[$period] ?? 1;
            $weightedSum += $grade * $weight;
            $totalWeight += $weight;
        }

        $average = $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0;

        return [
            'average' => $average,
            'status' => $this->getStatus($average),
            'remarks' => $this->getRemarks($average),
        ];
    }

    private function getStatus(float $grade): string
    {
        return match (true) {
            $grade >= 90 => 'Excellent',
            $grade >= 80 => 'Very Good',
            $grade >= 70 => 'Good',
            $grade >= 60 => 'Satisfactory',
            $grade >= 50 => 'Needs Improvement',
            default => 'Failing',
        };
    }

    private function getRemarks(float $grade): string
    {
        return match (true) {
            $grade >= 90 => 'Outstanding performance',
            $grade >= 80 => 'Commendable performance',
            $grade >= 70 => 'Good performance',
            $grade >= 60 => 'Acceptable performance',
            $grade >= 50 => 'Below expectations',
            default => 'Unsatisfactory performance',
        };
    }
}
