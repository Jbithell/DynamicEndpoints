<?php
namespace DynamicEndpointUtils;
class ParsingUtils {
    static function splitAtSlash($url) {

      $working = Array();
      $formatSplit = Array();
      foreach (str_split($url) as $char) {
        if ($char == "/") {
          if (implode($working) != "")
            $formatSplit[] = urldecode(implode($working));
          $working = Array();
        }
        else {
          $working[] = $char;
        }
      }
      $formatSplit[] = urldecode(implode($working));
      return array_filter($formatSplit);

    }

    static function matchAlongTemplate($_template, $_match) {
      $match = ParsingUtils::splitAtSlash($_match);
      $template = ParsingUtils::splitAtSlash($_template);
      if (count($template) != count($match)) return false;
      $search = true;
      $i = 0;
      $variables = Array();

      while ($search) {

        if (!array_key_exists($i, $template) || !array_key_exists($i, $match)) {$search = false; break;}
        if ($template[$i] == "..") {}
        else if ($template[$i][0] == "%") {
            $variables[$template[$i]] = $match[$i];
        }
        else if ($template[$i] != $match[$i]) {
            return false;
        }
        $i+=1;
      }

      if (!$variables) $variables = Array("nothing" => TRUE);
      return $variables;

    }

    static function elementMatches ($template, $match) {    
        //var_dump($template);
        //var_dump($match);
        if ($match == "") {
            return FALSE;
        }
        else if ($template == $match){
            return 3;
        }
        else if ($template[0] == "%"){
            return 2;
        }
        else if ($template == ".."){
            return 1;
        }
        return FALSE;
    }

    static function getBestMatch($templateStrings, $matchString) {

        $templates = array_map(["DynamicEndpointUtils\\ParsingUtils","splitAtSlash"], $templateStrings);

        global $match;
        $match = ParsingUtils::splitAtSlash($matchString);

        global $i;
        $i = 0;

        $search = TRUE;

        while ($search) {

            $finalEndpoint = NULL;

            if (!array_key_exists($i, $match)) {
                $search = FALSE;
                if (count($templates) == 1) {
                    $finalEndpoint = array_values($templates)[0];
                    break;
                }
                else if (count($templates) == 0){
                    return error("No endpoints");
                }
                else {
                    //var_dump($templates);
                    $shortest = Utils::getShortestArray($templates);
                    //var_dump($shortest);
                    if (count($shortest) > 1) return error("Muliple endpoints");
                    return array_values($shortest)[0];
                }
            }

            $templates = array_filter($templates, function ($template) {
                global $i;global $match;
                if (!array_key_exists($i, $template)) return FALSE;
                return ParsingUtils::elementMatches($template[$i], $match[$i]) != FALSE;
            });
            if (count($templates) < 1) {
                //echo("00");
                $search = FALSE;
                break;
            }
            $theseElems = array_map(function ($template) {
                global $i;
                return $template[$i];
            }, $templates);
            $currentElem = $match[$i];
            $i++;

        }
        if ($finalEndpoint == NULL) return error("No endpoint was matched");
        return $finalEndpoint;

    }
}
