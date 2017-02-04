<?php
/**
* Class Queue
* @author Budi Pratama <irezpratama90@gmail.com>
*
*/
Class Queue{

	protected 	$serv_time 			= 4;
	protected 	$time_of_arrival 	= 0;
	protected 	$start_service;
	protected 	$end_service 		= 0;
	protected 	$int_arrv_time;
	protected 	$time_in_queue 		= 0;
	protected 	$operation_time;
	protected 	$avgTimeInQueue;
	protected 	$total;
	protected   $total_time_in_queue;
	public function __construct($int_arrv_time)
	{
		$this->int_arrv_time = $int_arrv_time;
	}

	public function execute()
	{
		# rule Server ada dua namun jika terjadi antrian antrian yg menumpuk maka server B akan di buka
		# selama tidak ada antrian yang numpuk maka hanya akan di kerjakan satu server saja
		
		$no = 1; # counter
		$this->operation_time = date('Y-m-d 09:00'); # jam buka
		$server = ['Server A', 'Server B'];# jumlah server ada dua dengan type data array numeric
		$index = 1; # trik untuk melihat antrian yang selanjutnya dengan memainkan index
		$sub = $this->int_arrv_time; # cloning antrian -> untuk cek penyelesaian antrian selanjutnya
		$task = $server[0];# tugas pertama server A yang melayani
		$queueTotal = count($this->int_arrv_time); # jumlah antrian
		$tmp_end_service_server_1 = 0; # hasil data sementara end service untuk server 1
		$tmp_end_service_server_2 = 0; # hasil data sementara end service untuk server 2
		$total_time_in_queue = 0;
		$data = '';
		# Perulangan Antrian yang akan di layani 
		foreach ($this->int_arrv_time as $key => $value) 
		{
			# akumulasi dari nilai dari IAT
			$this->time_of_arrival+=$value;
			
			# menentukan start service dan end service
			# jika task = Server A
			if ($task == $server[0]) {
				# jika $tmp_end_service_server_1 lebih besar dari $this->time_of_arrival
				# maka start_service nya = $this->time_of_arrival, jika salah start_service = $tmp_end_service_server_1;
				if ($tmp_end_service_server_1 < $this->time_of_arrival)
				{
					$this->start_service 	= $this->time_of_arrival;
					$this->time_in_queue 	= 0;					
				} 
				else
				{
					$this->start_service    = $tmp_end_service_server_1;
					$this->time_in_queue 	= $tmp_end_service_server_1-$this->time_of_arrival;# time_in_queue = end_service - tmp_end_service_server_1
				}

				$this->end_service = $this->start_service + $this->serv_time;# start_service + serv_time
				$tmp_end_service_server_1 = $this->end_service;# simpan end_service ke tmp_end_service_server_1 
				#$this->time_in_queue = $this->end_service - $this->time_of_arrival;
			}
			else
			{
				if ($tmp_end_service_server_2 < $this->time_of_arrival)
				{
					$this->start_service = $this->time_of_arrival;
					$this->time_in_queue 	= 0;
				} 
				else
				{
					$this->start_service = $tmp_end_service_server_2;
					$this->time_in_queue 	= $tmp_end_service_server_1-$this->time_of_arrival;# time_in_queue = end_service - tmp_end_service_server_2
				}

				$this->end_service = $this->start_service + $this->serv_time;# start_service + serv_time
				$tmp_end_service_server_2 = $this->end_service;	# simpan end_service ke tmp_end_service_server_2
			}

			# date('Y-m-d H:i') format tanggal 
			# strtotime("+{$this->start_service} minutes",strtotime($this->operation_time)) start_service + jam buka
			$jam_melayani 	= date('Y-m-d H:i', strtotime("+{$this->start_service} minutes",strtotime($this->operation_time)));#konversi ke jam 
			$jam_selesai 	= date('Y-m-d H:i', strtotime("+{$this->end_service} minutes",strtotime($this->operation_time)));#konversi ke jam
			$jam_toa 		= date('Y-m-d H:i', strtotime("+{$this->time_of_arrival} minutes",strtotime($this->operation_time)));#konversi ke jam

			$total_time_in_queue+=$this->time_in_queue;
			// echo "|$index\t|$value\t|{$this->serv_time}\t|$jam_toa\t|$task\t|$jam_melayani\t|$jam_selesai\t|{$this->time_in_queue}  |\t\n";
			$data .= "<tr><td>$index</td><td>$value</td><td>{$this->serv_time}</td><td>$jam_toa</td><td>$task</td><td>$jam_melayani</td><td>$jam_selesai</td><td>{$this->time_in_queue}</td></tr>";
			// |$index\t|$value\t|{$this->serv_time}\t|$jam_toa\t|$task\t|$jam_melayani\t|$jam_selesai\t|{$this->time_in_queue}  |\t\n";

			# kondisi ini di pakai untuk menghindari error
			# jika antrian sekarang yg ke terakhir adalah 9 maka tidak dapat mendapatkan nilai yg selanjutnya
			if ($queueTotal > $index) 
				$end_time_2 = $this->time_of_arrival + $sub[$index];# waktu selesai untuk antrian selanjutnya


			# jika task = server B 
			if ($server[1] == $task) 
			{
				# jika end_service lebih besar dari time_of_arrival maka task selanjutnya di kerjakan oleh server A
				if ($this->end_service > $this->time_of_arrival) 
					$task = $server[0];# memberikan Antrian selanjutnya oleh server A
			}
			else
			{
				# bandingkan waktu penyelesaian sekarang dan waktu penyelesain selanjutnya, untuk menentukan server yang akan melayani antrian selanjutnya
				if ($this->end_service > $end_time_2) 
					$task = $server[1]; # memberikan Antrian selanjutnya oleh server B
				else
					$task = $server[0];	# memberikan Antrian selanjutnya oleh server A	
			}
		
			$index++;
		}
		
		$avgTimeInQueue = $total_time_in_queue/($index-1);
		$total = $this->serv_time * ($index-1);

		$this->setAverageTime($avgTimeInQueue);
		$this->setTotal($total);
		$this->setTimeInQueue($total_time_in_queue);
		return $data;
	}

	protected function setTimeInQueue($val)
	{
		$this->total_time_in_queue = $val;
	}
	
	protected function setAverageTime($avg)
	{
		$this->avgTimeInQueue = $avg;
	}

	protected function setTotal($total)
	{
		$this->total = $total;
	}

	public function getAverage()
	{
		return $this->avgTimeInQueue;
	}

	public function getTotal()
	{
		return $this->total;
	}

	public function getTotalTimeInQueue()
	{
		return $this->total_time_in_queue;
	}

	
}

$object = new Queue([5,3,16,5,1,2,3,17,2,5]);
$data = $object->execute();
?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="gaya_gua.css">
</head>
<body>
<section>
  <!--for demo wrap-->
  <h1>Hand simulation of a g/d/2 (fifo) queue (with out feedback)</h1>
  <div class="tbl-header">
    <table cellpadding="0" cellspacing="0" border="0">
      <thead>
        <tr>
          <th>No</th>
          <th>Inter Arrival Time</th>
          <th>Servc Time</th>
          <th>Time Of Arrival</th>
          <th>Server Assigned</th>
          <th>Start Service</th>
          <th>End Service</th>
          <th>Time In Queue</th>
        </tr>
      </thead>
    </table>
  </div>
  <div class="tbl-content">
    <table cellpadding="0" cellspacing="0" border="0">
      <tbody>
      <?= $data?>
      <tr><td>Total</td><td></td><td><?=$object->getTotal()?></td><td></td><td></td><td></td><td></td><td><?=$object->getTotalTimeInQueue()?></td></tr>
      </tbody>
    </table>
  </div>
</section>


<!-- follow me template -->
<div class="made-with-love">
  Created By
  <a target="_blank" href="https://github.com/budipratama">Budi Pratama</a>
</div>
</body>
</html>