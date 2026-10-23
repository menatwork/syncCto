<?php

/**
 * This file is part of menatwork/synccto.
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

namespace MenAtWork\SyncCto\Helper;

/**
 * Compare and transfer the data of tl_files between two installations.
 *
 * All fields are used, except the ignored ones. So fields of other bundles,
 * e.g. alternatives or important parts, are covered as well.
 */
class DbafsData
{
    /**
     * Get the fields, which have different values on both sides.
     *
     * Only fields, which exist on both sides, are compared. A field of a bundle, which is
     * installed only on one side, is no difference.
     *
     * @param array $source       The data of the source.
     *
     * @param array $target       The data of the target.
     *
     * @param array $ignoreFields Fields which are not compared.
     *
     * @return string[] The names of the different fields.
     */
    public static function getDifferences(array $source, array $target, array $ignoreFields): array
    {
        $differences = [];
        foreach (array_keys($source) as $field) {
            if (\in_array($field, $ignoreFields, true) || !\array_key_exists($field, $target)) {
                continue;
            }

            if (self::normalize($source[$field]) !== self::normalize($target[$field])) {
                $differences[] = $field;
            }
        }

        return $differences;
    }

    /**
     * Get the data of the source, which should be written to the target.
     *
     * @param array    $source       The data of the source.
     *
     * @param string[] $targetFields The fields of tl_files on the target.
     *
     * @param array    $ignoreFields Fields which are not transferred.
     *
     * @return array The data for the target.
     */
    public static function getTransferData(array $source, array $targetFields, array $ignoreFields): array
    {
        $data = [];
        foreach ($source as $field => $value) {
            if (\in_array($field, $ignoreFields, true) || !\in_array($field, $targetFields, true)) {
                continue;
            }

            $data[$field] = $value;
        }

        return $data;
    }

    /**
     * Normalize a value for the comparison.
     *
     * Depending on the database driver a number could be an integer or a string, so scalar
     * values are compared as string. NULL stays NULL and is not the same as an empty string,
     * otherwise the MetaWizard fails with an empty string.
     *
     * @param mixed $value The value.
     *
     * @return string|null
     */
    private static function normalize($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (\is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (\is_scalar($value)) {
            return (string) $value;
        }

        return serialize($value);
    }
}
