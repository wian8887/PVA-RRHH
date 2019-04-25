<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
// use App\Employee;
// use App\User;
use ZKLibrary;

class BioController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $biometricos= [
        'LP-1'		=>	'192.168.200.199', // la paz centrak
        'LP-2'	=>	'',	//la paz fondos
        'LP-3'	=>	'', //la paz almacen
        'PN'	=>	'',
        'BN'	=>	'',
        'CB'	=>	'',
        'SC'	=>	'',
        'OR'	=>	'',
        'PT'	=>	'',
        'CH'	=>	'',
        'TJ'	=>	'',
        ];
    $data = [];
    foreach ($biometricos as $ubicacion => $biometrico) {
        if ( $biometrico ) {
            $zk = new ZKLibrary($biometrico, 4370);    
            $zk->connect();
            //$zk->disableDevice();
            $users = $zk->getAttendance();
            $data[] = $users;
        }
    }
    return $data;
    // return 'hola bio';
  }

  
}
