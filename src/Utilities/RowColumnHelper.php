<?php

namespace Rcsofttech85\FileHandler\Utilities;

trait RowColumnHelper
{
    /**
     * @param array<string> $headers
     * @param array<string> $hideColumns
     * @return array<int<0, max>,int>
     */
    private function setColumnsToHide(array &$headers, array $hideColumns): array
    {
        $indices = [];
        if (!empty($hideColumns)) {
            foreach ($hideColumns as $hideColumn) {
                $index = array_search($hideColumn, $headers);
                if ($index !== false) {
                    $indices[] = (int)$index;
                    unset($headers[$index]);
                }
            }
            $headers = array_values($headers);
        }
        return $indices;
    }

    /**
     * @param array<int,string> $row
     * @param array<int<0, max>, int> $indices
     * @return void
     */
    private function removeElementByIndex(array &$row, array $indices): void
    {
        foreach ($indices as $index) {
            if (isset($row[$index])) {
                unset($row[$index]);
            }
        }

        $row = array_values($row);
    }
}
