<?php
namespace NikoNyrh\Hyphenator\Tests;

class HyphenatorTest extends \PHPUnit_Framework_TestCase
{
	public function testHyphenation()
	{
		$hyphenator = new \NikoNyrh\Hyphenator\Hyphenator('-');
		
		$words = array(
		    'algorithm'     => 'al-go-rithm',
		    'ScandinaviA'   => 'Scan-di-naviA', // case is preserved
		    'technically'   => 'tech-ni-cally',
		    'peculiarities' => 'pe-cu-liar-ities'
		);
		
		$inputs    = array_keys($words);
		$expecteds = array_values($words);
		
		$results = $hyphenator->hyphenate($inputs);
		
		foreach ($expecteds as $i => $expected) {
		    $this->assertEquals($expected, $results[$i]);
		}
	}
	
	public function testHtmlHyphenation()
	{
		$hyphenator = new \NikoNyrh\Hyphenator\Hyphenator('-');
		
		// HTML <tags/> and &speicial; constructs are preserved
	    $input    = '<hyphenated hyphenated style="hyphenated">hyphenated <hyphenated/> but not &hyphenated;';
	    $expected = '<hyphenated hyphenated style="hyphenated">hy-phen-ated <hyphenated/> but not &hyphenated;';
	    
	    $result = $hyphenator->hyphenate($input);
		$this->assertEquals($expected, $result);
	}
}
