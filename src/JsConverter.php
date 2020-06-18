<?php

/**
 * ovidigital/js-object-to-json
 *
 * @author Ovi Indrei <ovi@ovidigital.com>
 * @copyright Copyright (c) 2020, Ovi Indrei
 * @license https://github.com/ovidigital/js-object-to-json/LICENSE.md MIT
 */

namespace OviDigital\JsObjectToJson;

class JsConverter
{
    /**
     * Converts a JavaScript object string to a JSON formatted string.
     *
     * @param string $jsObjectString The JavaScript object
     * @return string
     */
    public static function convertToJson(string $jsObjectString)
    {
        $replacedStringsList = [];

        // 1. Replace all delimited string literals with placeholders
        $convertedString = self::replaceSectionsWithPlaceholders($jsObjectString, $replacedStringsList, "'");
        self::fixQuoteEscapingForSingleQuoteDelimitedStrings($replacedStringsList);
        $convertedString = self::replaceSectionsWithPlaceholders($convertedString, $replacedStringsList, '"');

        // 2. Now is safe to remove all white space
        $convertedString = preg_replace('/\s+/m', '', $convertedString);

        // 3. Add double quotes for keys
        $convertedString = preg_replace('/([^{}\[\]#,]+):/', '"$1":', $convertedString);

        // 4. Add double quotes for values
        $convertedString = preg_replace_callback(
            '/:([^{}\[\]#,]+)/',
            function ($matches) {
                if (is_numeric($matches[1])) {
                    return ':' . $matches[1];
                } else {
                    return ':"' . $matches[1] . '"';
                }
            },
            $convertedString
        );

        // 5. Make sure "true", "false" and "null" values get delimited by double quotes
        // Need to run the replacement twice, because not all values get replaced if they are adjacent
        $convertedString = preg_replace('/([^"])(true|false|null)([^"])/', '$1"$2"$3', $convertedString);
        $convertedString = preg_replace('/([^"])(true|false|null)([^"])/', '$1"$2"$3', $convertedString);

        // 6. Replace the placeholders with the initial strings
        $deep = false;
        do {
            $convertedString = preg_replace_callback(
                '/###(\d+)###/',
                function ($matches) use (&$replacedStringsList, $deep) {
                    $replace = $replacedStringsList[$matches[1]];
                    unset($replacedStringsList[$matches[1]]);
                    return ($deep ? "'" . $replace . "'" : '"' . $replace . '"');
                },
                $convertedString
            );
            $deep = true;
        } while (!empty($replacedStringsList));

        return $convertedString;
    }

    /**
     * Replaces sections enclosed by a specified delimiter with placeholders of form '###<PLACEHOLDER_INDEX>###'.
     *
     * @param string $input The string input
     * @param array $replacedSectionsList The replaced sections will be added to this array
     * @param string $delimiter The delimiter that encloses the sections (e.g. "'" - single quote)
     * @param bool $removeDelimitersFromSections Flag to remove or preserve the delimiters for the sections
     * @return string
     */
    protected static function replaceSectionsWithPlaceholders(
        string $input,
        array &$replacedSectionsList,
        string $delimiter,
        bool $removeDelimitersFromSections = true
    ) {
        $output = $input[0];
        $sectionStartPos = $sectionEndPos = -1;
        $contentCopiedUntilPos = 0;

        for ($i = 1; $i < strlen($input) - 1; $i++) {
            $char = $input[$i];
            $prevChar = $input[$i - 1];

            if ($char === $delimiter && $prevChar !== '\\') {
                if ($sectionStartPos === -1) {
                    $sectionStartPos = $i;
                } elseif ($sectionEndPos === -1) {
                    $sectionEndPos = $i;
                }
            }

            // If a section has been identified
            if ($sectionEndPos > -1) {
                $output .= substr($input, $contentCopiedUntilPos + 1, $sectionStartPos - $contentCopiedUntilPos - 1);

                // Replace section with placeholder
                $output .= '###' . count($replacedSectionsList) . '###';

                // Extract the section and add it to the replaced sections list
                if ($removeDelimitersFromSections) {
                    $section = substr($input, $sectionStartPos + 1, $sectionEndPos - $sectionStartPos - 1);
                } else {
                    $section = substr($input, $sectionStartPos, $sectionEndPos - $sectionStartPos);
                }
                $replacedSectionsList[] = $section;

                // Update relevant local vars
                $contentCopiedUntilPos = $sectionEndPos;
                $sectionStartPos = -1;
                $sectionEndPos = -1;
            }
        }

        $output .= substr($input, $contentCopiedUntilPos + 1);

        return $output;
    }

    /**
     * Fix the escaping for quotes inside strings that were initially delimited by single quotes.
     *
     * For example:
     * ```
     *  'string containing \' single quote' => "string containing ' single quote"
     *  'string containing " double quote' => "string containing \" double quote"
     * ```
     * @param array $strings
     */
    protected static function fixQuoteEscapingForSingleQuoteDelimitedStrings(array &$strings)
    {
        foreach ($strings as &$string) {
            // Escape contained double quotes
            $string = preg_replace('/([^\\\])"/', '$1\"', $string);
            // Unescape contained single quotes
            $string = preg_replace("/\\\\'/", "'", $string);
        }
    }
}
