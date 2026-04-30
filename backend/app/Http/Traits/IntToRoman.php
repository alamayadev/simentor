<?php

namespace App\Http\Traits;

trait IntToRoman
{
    private function intToRoman($num) {
        $mapping = [
            1000 => 'M',
            900 => 'CM',
            500 => 'D',
            400 => 'CD',
            100 => 'C',
            90 => 'XC',
            50 => 'L',
            40 => 'XL',
            10 => 'X',
            9 => 'IX',
            5 => 'V',
            4 => 'IV',
            1 => 'I'
        ];

        $result = '';

        foreach ($mapping as $value => $roman) {
            while ($num >= $value) {
                $result .= $roman;
                $num -= $value;
            }
        }

        return $result;
    }
    private function integerToRoman($integer)
    {
     // Convert the integer into an integer (just to make sure)
     $integer = intval($integer);
     $result = '';

     // Create a lookup array that contains all of the Roman numerals.
     $lookup = array('M' => 1000,
     'CM' => 900,
     'D' => 500,
     'CD' => 400,
     'C' => 100,
     'XC' => 90,
     'L' => 50,
     'XL' => 40,
     'X' => 10,
     'IX' => 9,
     'V' => 5,
     'IV' => 4,
     'I' => 1);

     foreach($lookup as $roman => $value){
      // Determine the number of matches
      $matches = intval($integer/$value);

      // Add the same number of characters to the string
      $result .= str_repeat($roman,$matches);

      // Set the integer to be the remainder of the integer and the value
      $integer = $integer % $value;
     }

     // The Roman numeral should be built, return it
     return $result;
    }
    function numberToRoman($num)
    {
        // Be sure to convert the given parameter into an integer
        $n = intval($num);
        $result = '';

        // Declare a lookup array that we will use to traverse the number:
        $lookup = array(
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
            'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
            'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
        );

        foreach ($lookup as $roman => $value)
        {
            // Look for number of matches
            $matches = intval($n / $value);

            // Concatenate characters
            $result .= str_repeat($roman, $matches);

            // Substract that from the number
            $n = $n % $value;
        }

        return $result;
    }
    /**
     * @param int $number
     * @return string
     */
    private function numberToRomanRepresentation($number) {
        $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }
        return $returnValue;
    }
}
