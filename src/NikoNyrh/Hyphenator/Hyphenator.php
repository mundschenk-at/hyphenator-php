<?php
namespace NikoNyrh\Hyphenator;

class Hyphenator {
    private $patterns;
    private $trie;
    private $hyphen;
    
    public function __construct($hyphen='&shy;', $patterns=null)
    {
        if ($patterns == null) {
            $patterns = __DIR__ . '/english.txt';
        }
        
        if (!is_array($patterns)) {
            $patterns = file($patterns);
        }
        
        $this->hyphen   = $hyphen;
        $this->patterns = array();
        
        // Build a Trie for supposedly efficient pattern look-up,
        // I haven't actually benchmarked this.
        foreach ($patterns as $pattern) {
            $pattern = trim($pattern);
            
            if (empty($pattern)) {
                continue;
            }
            
            $key = preg_replace('/[0-9]/', '', $pattern);
            $this->patterns[$key] = $pattern;
            
            $node = &$this->trie;
            foreach (str_split($key) as $char) {
                if (!isset($node[$char])) {
                    $node[$char] = array();
                }
                $node = &$node[$char];
            }
            
            preg_match_all('/([0-9]+)/', $pattern, $offsets, PREG_OFFSET_CAPTURE);
            
            $node['_pattern'] = array(
                'pattern' => $pattern,
                'offsets' => $offsets[1]
            );
        }
    }
    
    public function hyphenate($string)
    {
        if (is_array($string)) {
            $result = array();
            foreach ($string as $key => $value) {
                $result[$key] = $this->hyphenate($value);
            }
            return $result;
        }
        
        $output = preg_split('/([^a-z]+)/i', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $step   = sizeof($output) >= 3 ? 2 : 1;
        
        $htmlTagCount = 0;
        
        // Every second array element is a word candidate for hyphenation but HTML construts need to be preserved.
        foreach (range(0, sizeof($output)-1, $step) as $key) {
            if ($key > 0) {
                if (preg_match('/</', $output[$key-1])) {
                    // HTML tag opens
                    $htmlTagCount++;
                }
                else if (preg_match('/>/', $output[$key-1])) {
                    // HTML tag closes
                    $htmlTagCount--;
                }
                else if (preg_match('/&$/', $output[$key-1]) && preg_match('/^;/', $output[$key+1])) {
                    // This was a &escaped; sequence
                    continue;
                }
            }
            
            if ($htmlTagCount > 0) {
                // Were are inside tags, leave intact
                continue;
            }
            
            $output[$key] = $this->hyphenateWithoutHtmlChecks($output[$key]);
        }
        
        return implode('', $output);
    }
    
    // This is meant to be called only from the hyphenate() method
    public function hyphenateWithoutHtmlChecks($word)
    {
        // Add underscores to make out-of-index checks unnecessary,
        // also hyphenation is done in lower case.
        $word   = '_' . $word . '_';
        $chars  = str_split(strtolower($word));
        $nChars = sizeof($chars);
        
        $patterns    = array();
        $breakpoints = array();
        
        for ($start = 0; $start < $nChars; ++$start) {
            // Start from the trie root node
            $node = &$this->trie;
            
            // Walk through the trie while storing detected patterns
            for ($step = $start; $step < $nChars; ++$step) {
                if (isset($node['_pattern'])) {
                    $patterns[] = array(
                        'start'  => $start,
                        'offset' => $step,
                        'pattern' => $node['_pattern']
                    );
                    
                    // Uh oh, I kind of forgot what happens here in detail
                    // but the max value for the offset is stored.
                    foreach ($node['_pattern']['offsets'] as $offsetIndex => $patternOffset) {
                        $value  = $patternOffset[0];
                        
                        // PREG_OFFSET_CAPTURE includes the numbers in
                        // offset calculations, so we need to compensate for it
                        $offset = $patternOffset[1] + $start - $offsetIndex;
                        $breakpoints[$offset] = isset($breakpoints[$offset])
                            ? max($breakpoints[$offset], $value)
                            : $value;
                    }
                }
                
                // No further path in the trie
                if (!isset($node[$chars[$step]])) {
                    break;
                }
                
                $node = &$node[$chars[$step]];
            }
        }
        
        //TODO: Make configurable...
        $minBegin   = 2;
        $minEnd     = 2;
        $minPrev    = 2;
        
        // Operate on the original $word, it has original letter cases
        $chars      = str_split($word);
        $hyphenated = '';
        $prevHyphen = 0;
        
        for ($i = 1; $i < $nChars-1; ++$i) {
            if (
                    // Confirm that the hyphenation would not be too short...
                    $i >= $minBegin+1 &&
                    $i <  $nChars-1-$minEnd &&
                    $i >= $prevHyphen + $minPrev &&
                    
                    // ... but this is the crucial point of the algorithm!
                    isset($breakpoints[$i]) &&
                    ($breakpoints[$i] % 2) == 1
            ) {
                $hyphenated .= $this->hyphen;
                $prevHyphen  = $i;
            }
            
            $hyphenated .= $chars[$i];
        }
        
        return $hyphenated;
    }
}

