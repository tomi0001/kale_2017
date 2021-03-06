<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Input;
use DB;
use Auth;
use Hash;
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function login() {
      if (!empty(Auth::User()->id))  return Redirect('login');
      return view('login');
      
    }
    public function login2($years = "",$month="",$day="",$action = "") {
    //Input::get('month');
	if (empty(Auth::User()->id)) {
	  return Redirect('error')->with('login_error','Musisz się zalogować');
	}
        if (empty($month) ) {
	  $month = date("m");
	  $years = date("Y");
	}
	if ( empty($day) and empty($action) ) {
	  $day = date("d");
	}
	else {
	  if ( !empty($day) ) {
	    $day = $day;
	  }
	  //$month = $month;
	  //$years = $years;
	}
	
	 //oblicza w kt rym dniu zacza  si  dany miesiac 
  if ( !empty($years) or  !empty($month)) {
      //$_GET["rok"] = atak_sql($_GET["rok"]);
      //$_GET["miesiac"] = atak_sql($_GET["miesiac"]);
      //$rok = $_GET["rok"];
      //$miesiac = $_GET["miesiac"];
      $day_week = $this->check_day_week("$years-$month-1");
  }
  else {
      $years = date("Y");
      $month = date("m");
      $day_week = $this->check_day_week("Y-m-1");
  }
  
  if ($day_week == 0) {
      $day_week = 7;
  }

  $day1 = 1;
  $day2 = 1;
  $day3 = "";
  if (!empty($action) ) {
    if ($action == "wstecz") {
      $day3 = $this->sum_how_day_month($month,$years);
      //$_GET["dzien"] = $dzien3;
    }
    if ($action == "dalej")  {
      $day3 = 1;
      //$_GET["dzien"] = 1;
    }
    $day = $day3;
  }
	

      $month2 = $this->return_month_text($month);
      $how_day_month = $this->sum_how_day_month($month,$years);
      $back = $this->return_back_month($month,$years);
      $next = $this->return_next_month($month,$years);
      
      $this->delete_differences(Auth::User()->id);
      if ($years == "" and $month == "" and $day == "") {  
	  $years = date('Y');
	  $month = date('m');
	  $day = date('d');
      }
    
    $start_day = $this->Calculate_date_beginning_of_the_day($years,$month,$day);
    $id_users = Auth::User()->id;
    //print $start_day[1];
    $select_drugs = DB::select("select id_substancji,porcja,data,rodzaj_porcji,data2,id from spozycie where id_usera = '$id_users'  and data >= '$start_day[0]' and data <= '$start_day[1]'  order by data");
    
    //$kolor = "<span class=normalna2>";
    //$kolor2 = "<span class=normalna3>";
    $kol = "<span ";
    $i = 1;
    $price = 0;
    $table_substances = array();
    $benzo = 0;
    $alcohol = 0;
    $select_drugs3 = array();
    $color = array();
    $color2 = array();
    $j = 0;
    foreach ($select_drugs as $select_drugs2 ) {
      $name_drugs = DB::select("select nazwa,cena,rodzaj_porcji,za_ile,color,rownowaznik,ile_procent,id from substancje where id = '" . $select_drugs2->id_substancji . "' ");
      foreach ($name_drugs as $name_drugs2) {}
      if ($name_drugs2->color != "") {
	$color[$j] = "font color=" . $name_drugs2->color ."";
	$color2[$j] = "<font color=" . $name_drugs2->color  . " size=2";
	
      }
      else {
	$color[$j] = "span class=normalna2";
	$color2[$j] = "<span class=normalna3";      
	
      }
      
      $select_drugs3[$j][0] = $name_drugs2->nazwa;
      $data_111 = explode(" ",$select_drugs2->data);
      $data_222 = explode("-",$data_111[0]);
      $data_333 = explode(":",$data_111[1]);
      $sum = mktime($data_333[0],$data_333[1],$data_333[2],$data_222[1],$data_222[2],$data_222[0]);
      //$tablica_substancji[$i-1][0] = $nazwa_substancji[0];
      //$tablica_substancji[$i-1][1] = $wybierz_leki2[1];
      //$tablica_substancji[$i-1][2] = $wybierz_leki2[3];
      $price2  = 0;
      $price = $this->sum_price_drugs($name_drugs2->za_ile,$select_drugs2->porcja,$name_drugs2->cena);
      $price2 += $price;
      $price = $this->sum_price($price);
      $type = $this->check_portion($name_drugs2->rodzaj_porcji);
      //$obiekt_data->oblicz_date($sum,$select_drugs->data);
      $j++;
    }
      
      return view('welcome')->with('month',$month)->with('years',$years)->with('day1',$day1)->with('day2',$day2)->with('day3',$day3)->with('how_day_month',$how_day_month)->with('day_week',$day_week)->with('day',$day)->with('month1',$month2)->with('back',$back)->with('next',$next)->with('select_drugs2',$select_drugs3)->with('color',$color)->with('color2',$color2);
    
    }
   private function check_portion($type_portion) {
    if ($type_portion == 1) return "mg";
    if ($type_portion == 2) return "mililitry";
    if ($type_portion == 3) return "ilości";
    //if ($rodzaj_porcji == 4) return "gramy";
    //if ($rodzaj_porcji == 5) return "mililitry";
    //if ($rodzaj_porcji == 6) return "litry";
  }
      private  function sum_price($number) {
    
      //$liczba_odwrotna = strrev($liczba);
	//print $liczba_odwrotna . "<br>";
      $number = round($number,2);
    
      if ( !strstr($number,".") ) {
	
	return $number . " zł";
      }
      else {
      

	$number2 = explode(".",$number);
	//$liczba2 = substr($liczba2[1],0,2);
	$number3 = strlen($number2[1]);
	if ($number3 == 1) {
	  $number2[1] .= "0";
	}
	if ( $number2[0][0] == 0) {
	  return $number2[1] . " gr";
	}
	else {
	  return $number2[0] . " zł i " . $number2[1] . " gr";
	}
      }
    
    
    
  }
  private function sum_price_drugs($for_much,$portion,$price) {
    if ($for_much == 0) return 0;
    return $price * $portion / $for_much;
  }
   private function delete_differences($id_users) {
    
    DB::delete("delete from roznica where id_usera = '$id_users' ");
    }
    public function logout() {
      Auth::logout();
      return Redirect('succes')->with('login_succes','Wylogowałeś się pomyślnie');
      
    }
    public function succes() {
      
      return view('succes');
    }
   private function Calculate_date_beginning_of_the_day($years,$month,$day) {

    $date_show = $years . "-" . $month . "-" . $day;
    $start_day = ' 05:00:00';
    $date_show2 = $date_show . $start_day;
    //$data_pokazania4 = $data_pokazania3 . " 05:00:00";
    $time_day = strtotime($date_show2);
    $time_day2 = date("Y-m-d H:i:s",$time_day);
    $time_day3 = date("Y-m-d H:i:s",$time_day+86400);
  
    return array($time_day2,$time_day3);
  
  }
    
   private function return_month_text($month) {
    
    switch($month) {
      case 1 : return "Styczeń";
      case 2 : return "Luty";
      case 3 : return "Marzec";
      case 4 : return "Kwiecień";
      case 5 : return "Maj";
      case 6 : return "Czerwiec";
      case 7 : return "Lipiec";
      case 8 : return "Sierpień";
      case 9 : return "Wrzesień";
      case 10 : return "Październik";
      case 11: return "Listopad";
      case 12 : return "Grudzień";
    }

  }
  
    private function return_back_month($month,$years) {
    if ($month == 1) {
      $years--;
      $month = 12;
    }
    else {
      $month--;
    }
    return array($years,$month);
  }

  private function return_next_month($month,$years) {
    if ($month == 12) {
      $years++;
      $month = 1;
    }
    else {
      $month++;
    }
    return array($years,$month);
  }
  
  private function check_day_week($data) {

      //$dzien_tyg = date("w",strtotime($data));
      //return $dzien_tyg; 
      return date("w",strtotime($data));
  }
  
  
    private function sum_how_day_month($month,$years) {

      if ($month == 12) {
	  return 31;
      }
      else if ($month == 11) {
	  return 30;
      }
      else if ($month == 10) {
	  return 31;
      }
      else if ($month == 9) {
	  return 30;
      }
      else if ($month == 8) {
	  return 31;
      }
      else if ($month == 7) {
	  return 31;
      }
      else if ($month == 6) {
	  return 30;
      }
      else if ($month == 5) {
	  return 31;
      }
      else if ($month == 4) {
	  return 30;
      }
      else if ($month == 3) {
	  return 31;
      }
      else if ($month == 2) {

	  if ( $this->if_accessible($years) == 1) {
	      return 29;
	  }
	  else {
	      return 28;
	  }

      }
      else if ($month == 1) {
	  return 31;
      }


  }
  
  private function if_accessible($years)
  {
       return (($years%4 == 0 && $years%100 != 0) || $years%400 == 0);
  }
  
  public function error() {
    
    return view('error');
    
    
    
  }
  
          public function login3() {

    $password = Input::get('password');
    //821c6e4d060725576d68717f2c4bd95429bbb848
    //$a = Hash::make("");
    //print $a;
    
  $user = array(
    'login' => Input::get('login'),
    'password' => $password
  );
  //var_dump($user);
  if (Input::get('login') == "" and Input::get('password') == "" ) {
    return Redirect('error')->with('login_error','Uzupełnij pole login i hasło');
    //print "3";
  }
  if (Auth::attempt($user))
  {
  //print Auth::User()->id;
    return Redirect('login');
  }
  else {
    //print Input::get('login');
    return Redirect('error')->with('login_error','Nieprawidłowy login lub hasło');
  }
    
    }
  
}

