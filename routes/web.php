<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/tes', 'loketController@tes');
	
Route::get('/login', 'LoginController@loginPage');
Route::post('/login', 'LoginController@login');
Route::get('/logout', 'LoginController@logout');

Route::group(['middleware' => 'login.auth'], function () {
	//cek	
	Route::get('/', 'LoginController@home');
	//Route::get('/transaksi/pdam', 'TransaksiController@listTransaksi');
	Route::get('/transaksi/pdam', 'TransaksiController@listTrans');
	Route::get('/transaksi/pdam/{tipe}/{jenis}/{loket}/{dari}/{sampai}', 'TransaksiController@ajaxTransaksiNewMultiple');
	Route::get('/detailTransaksi/{loket}/{tanggal}/{user}', 'TransaksiController@ajaxDetailTransaksiNew');

	Route::get('/transaksi/pdam/cetakDetail/{loket}/{tanggal}/{user}', 'TransaksiController@cetakDetailPdamNew');
	Route::get('/transaksi/pdam/cetakRekapExcellDetail/{tipe}/{jenis}/{loket}/{dari}/{sampai}', 'TransaksiController@cetakRekapExcellDetailNew');
	Route::get('/transaksi/pdam/cetakRekapExcell/{tipe}/{jenis}/{loket}/{dari}/{sampai}', 'TransaksiController@cetakRekapExcell'); //new
	Route::get('/transaksi/pdam/cetakPdf/{tipe}/{jenis}/{loket}/{dari}/{sampai}', 'TransaksiController@cetakPdfNew');
	Route::get('/ajaxLoketsTipe/{tipe}','loketController@ajaxLoketsTipe');
	
	//h1
	Route::get('/transaksi/h1', 'TransaksiController@transaksiH1');
	Route::get('/transaksi/h1/ajaxH1/{tipe}', 'TransaksiController@ajaxTransaksiH1new');
	//rekon
	Route::get('/transaksi/rekonsiliasi', 'TransaksiController@Rekonsiliasi');
	Route::get('/transaksi/rekonsiliasi/{tanggal}', 'TransaksiController@RekonsiliasiPostNew');

	Route::get('/transaksi/rekon/detail/{jenis_loket}/{tanggal}', 'TransaksiController@detailRekon');
	Route::get('/transaksi/rekon/cetakDetail/{tanggal}/{jenis_loket}', 'TransaksiController@cetakDetail');
	Route::post('/transaksi/rekonsiliasi/simpanStatus','TransaksiController@ajaxSimpanStatus');

	//deposit
	Route::get('/deposit/loket', 'DepositController@depositLoket');
	Route::get('/deposit/loket/{tipe}', 'DepositController@ajaxDepositLoket');
	Route::get('/deposit/mutasi', 'DepositController@depositMutasi');
	Route::get('/deposit/mutasi/{bulan}', 'DepositController@ajaxDepositMutasi');
		
	//setting
	Route::get('/setting/user', 'UserController@setupUser');
	Route::get('/setting/listUser', 'UserController@ajaxListUser');
	Route::post('/setting/setupUser/simpanUser', 'UserController@ajaxSimpanUser');
	Route::get('/setting/setupUser/{id}', 'UserController@ajaxUser');
	Route::post('/setting/setupUser/hapusUser/{id}', 'UserController@ajaxHapusUser');

	Route::get('/setting/menu', 'menuController@setupMenu');
	Route::get('/setting/listMenu', 'menuController@ajaxListMenu');
	Route::get('/setting/setupMenu/{id}', 'menuController@ajaxMenu');
	Route::post('/setting/setupMenu/simpanMenu', 'menuController@ajaxSimpanMenu');
	Route::post('/setting/setupMenu/hapusMenu/{id}', 'menuController@ajaxHapusMenu');

	Route::get('/setting/permission', 'menuController@setupPermission');
	Route::get('/setting/ajaxPermission/{id}','menuController@ajaxPermission');
	Route::post('/setting/simpanPermission/{level}', 'menuController@ajaxSimpanPermission');

	Route::get('/setting/jenisLoket', 'loketController@listLoket');
	Route::get('/ajaxLokets/{id}','loketController@ajaxLokets');
	Route::post('/setting/simpanLoket', 'loketController@ajaxSimpanLoket');
	Route::get('/setting/ajaxLoket', 'loketController@ajaxListLoket');

	//laporan
	Route::get('/laporan/bulanan', 'laporanController@listBulanan');
	Route::get('/laporan/ajaxlapBulanan/{tahun}/{bulan}/{loket_code}/{jenis_transaksi}/{tipe}', 'laporanController@ajaxListBulananNew');
	
	Route::get('laporan/rekapFee','laporanController@rekapFee');
	Route::get('laporan/bulanan/rekapKasir/{bulan}','laporanController@rekapKasirBulanan');
	Route::get('laporan/bulanan/rekapFee/{bulan}','laporanController@rekapFeeBulanan');
	Route::get('laporan/bulanan/laporanPendapatan/{bulan}','laporanController@laporanPendapatan');

	//Route::get('/laporan/bulanan/pdf', 'laporanController@testpdf');
	Route::get('laporan/bulanan/pdfDetail/{tahun}/{bulan}/{loket_code}/{jenis_transaksi}','laporanController@pdfBulananNew');
	Route::get('laporan/bulanan/pdf/{tahun}/{bulan}/{loket_code}/{jenis_transaksi}/{tipe}','laporanController@pdfNew');
	Route::get('laporan/bulanan/excellDetail/{tahun}/{bulan}/{loket_code}/{jenis_transaksi}','laporanController@excellBulananNew');

	//peta
	Route::get('/peta', 'petaController@peta');
	Route::get('/peta/ajaxPeta', 'petaController@ajaxPeta');
	Route::post('/peta/simpanPeta', 'petaController@simpanPeta');

	Route::get('/peta/listLoket', 'petaController@listLoket');
	Route::get('/peta/ajaxListLoket', 'petaController@ajaxListLoket');
	Route::get('/peta/listLoket/{id}', 'petaController@getLoket');
	Route::post('/peta/hapusLoketPeta/{id}', 'petaController@hapusLoket');

	//



});	
