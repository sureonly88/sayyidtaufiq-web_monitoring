<html>
<head>
	
	<title></title>
	<style>
	table, th, td {
	    border: 1px solid black;
	    border-collapse: collapse;
	}
	th, td {
	    padding: 5px;
	}
	</style>
</head>
<body >
	<div>
		<table style="width:100%">
			<tr>
				<th rowspan="2" align="left" style="border:0px;" width="50" ><img src="{{ asset('image/logo.jpg') }}" width="60" alt="PDAM" /></th>
				<th align="left" style="border:0px;">Koperasi Konsumen PEDAMI</th>
			</tr>
			<tr>
				<th align="left" style="border:0px;">Banjarmasin</th>
			</tr>
			<tr>
				<th colspan="2" align="left" style="border:0px;">REKAP FEE ADMIN TRANSAKSI PAYMENT POINT</th>
			</tr>
			<tr >
				<th colspan="2" align="left" style="border:0px;">Bulan {{$bulan}} {{$tahun}}</th>
			</tr>	
		</table>

	</div>
	<div>
		<table style="width:100%">
			<thead>
				<tr>
					<th>No</th>
					<th>Tahun</th>
					<th>Bulan</th>
					<th>Jenis Transaksi</th>
					<th>Nama Loket</th>
					<th>Jumlah</th>
					<th>Fee Admin</th>
				</tr>
			</thead>
			<tbody >
			<?php $x=1; 
				$jlhjumlah=0;
				$jlhjumlah_share=0;
			?>	
			@foreach($list as $r)
			<tr>
				<td align="center" >{{ $x }}</td>
				<td align="left" >{{ $tahun }}</td>
				<td align="left" >{{ $bulan }}</td>
				<td align="left" >{{ $r->jenis_transaksi }}</td>
				<td align="left" >{{ $r->loket_name }}</td>
				<td align="right" >{{ number_format($r->jumlah) }}</td>
					<?php $setor=0;
						//byadmin
						if ($r->jenis_transaksi!='PDAM_BANDARMASIH'){
							$byadmin=2500;
						}else{
							$byadmin=$r->byadmin;
						}
						//
						if($r->jenis_transaksi=='PDAM_BANDARMASIH'){
							$share=$r->pdam;
						}else if($r->jenis_transaksi=='PLN_POSTPAID'||$r->jenis_transaksi=='PLN_POSTPAID_N'){
							$share=$r->pln_postpaid;
						}else if($r->jenis_transaksi=='PLN_PREPAID'||$r->jenis_transaksi=='PLN_PREPAID_N'){
							$share=$r->pln_prepaid;
						}else if($r->jenis_transaksi=='PLN_NONTAGLIS'||$r->jenis_transaksi=='PLN_NONTAGLIS_N'){
							$share=$r->pln_nontaglis;
						}
						//

						$jumlah_share=$share*$r->jumlah;
						//sum
						$jlhjumlah+=$r->jumlah;
						$jlhjumlah_share+=$jumlah_share;
					?>
				<td align="right" >{{ number_format($jumlah_share) }}</td>
			</tr>
			<?php $x++;?>
			@endforeach	
			</tbody>
			<tfoot>
				<tr>
					<th colspan="5"  align="right">Total</th>
					<th  align="right">{{ number_format($jlhjumlah) }}</th>
					<th  align="right">{{ number_format($jlhjumlah_share) }}</th>
				</tr>
			</tfoot>	
		</table>
	</div>
	
</body>
</html>