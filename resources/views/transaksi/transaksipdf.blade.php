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
				<th colspan="2" align="left" style="border:0px;">LPP</th>
			</tr>
			<tr >
				<th colspan="2" align="left" style="border:0px;">Tanggal : {{$tanggal}}</th>
			</tr>	
		</table>

	</div>
	<div>
		<table style="width:100%">
			<thead>
				<tr>
					<th>No</th>
					<th>Tanggal</th>
					<th>Nama Loket</th>
					<th>User</th>
					<th>Jenis Transaksi</th>
					<th>Jumlah</th>
					<th>Tagihan</th>
					<th>Admin</th>
					<th>Total</th>
				</tr>
			</thead>
			<tbody >
			<?php  
				$jlhtagihan=0;
				$jlhjumlah=0;
				$jlhadmin=0;
				$jlhtotal=0;
			?>	
			@foreach($list as $no => $r)
			<tr>
				<td align="center" >{{ ++$no }}</td>
				<td align="left" >{{ $r->tanggal }}</td>
				<td align="left" >{{ $r->loket_name }}</td>
				<td align="left" >{{ $r->user_ }}</td>
				<td align="left" >{{ $r->jenis_transaksi }}</td>
				<td align="right" >{{ number_format($r->jumlah) }}</td>
				<td align="right">{{ number_format($r->tagihan) }}</td>
				<td align="right" >{{ number_format($r->admin) }}</td>
				<td align="right">{{ number_format($r->total) }}</td>
					<?php $setor=0;
						//sum
						$jlhtagihan+=$r->tagihan;
						$jlhjumlah+=$r->jumlah;

						$jlhadmin+=$r->admin;
						$jlhtotal+=$r->total;
					?>
			</tr>
			@endforeach	
			</tbody>
			<tfoot>
				<tr>
					<th colspan="5"  align="right">Total</th>
					<th  align="right">{{ number_format($jlhjumlah) }}</th>
					<th  align="right">{{ number_format($jlhtagihan) }}</th>
					<th  align="right">{{ number_format($jlhadmin) }}</th>
					<th  align="right">{{ number_format($jlhtotal) }}</th>
				</tr>
			</tfoot>	
		</table>
	</div>
	
</body>
</html>