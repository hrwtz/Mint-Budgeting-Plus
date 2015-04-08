<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('get_months_between_dates')) :
    function get_months_between_dates($start_date, $end_date){
        $start_date = explode('-', $start_date);
        $end_date = explode('-', $end_date);
        $count = (($end_date[0] - $start_date[0] ) * 12) - ($start_date[1] - $end_date[1]);
        $count ++;
        return $count;
    }   
endif;

if ( ! function_exists('get_previous_month')) :
    function get_previous_month($date){
    	$date = new DateTime($date);
		$date->sub(new DateInterval('P1M'));
		return $date->format('Y-m-d');
    }   
endif;

if ( ! function_exists('get_next_month')) :
    function get_next_month($date){
    	$date = new DateTime($date);
		$date->add(new DateInterval('P1M'));
		return $date->format('Y-m-d');
    }   
endif;

if ( ! function_exists('get_end_of_month')) :
    function get_end_of_month($date){
        $date = new DateTime($date);
        return $date->format('Y-m-t');
    }   
endif;