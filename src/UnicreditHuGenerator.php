<?php
	
	namespace Greksazoo\UnicreditTransferImportGeneratorHu;
	
	use Carbon\Carbon;
	use Illuminate\Support\Facades\Storage;
	
	class UnicreditHuGenerator
	{
		private $own_data;
		private $transfers;
		private $recordsum;
		private $accountsum;
		private $amountsum;
		
		public function __construct ($own_data, $transfers_data)
		{
			$this->own_data = (object)$own_data;
			$this->transfers = $transfers_data;
			$this->recordsum = 0;
			$this->accountsum = '000000000000000000000000';
			$this->amountsum = 0;
		}
		
		public function generateText ()
		{
			$currency = $this->get_currency($this->own_data);
			$text = '';
			$text .= $this->gen_header($currency);
			$text .= $this->gen_data($currency);
			$text .= $this->gen_trailer($currency);
			
			return $text;
		}
		
		public function generateFile ()
		{
			$text = $this->generateText();
			$textFile = time() . '_file.text';
			Storage::put(config('unicredithugenerator.path') . '/' . $textFile, $text);
			
			return config('unicredithugenerator.path') . '/' . $textFile;
		}
		
		private function gen_header ($currency): string
		{
			
			$elso = '';
			if ($currency === 'HUF')
			{
				$elso .= '23'; //rekordtípus
				$elso .= $this->get_account_number($this->own_data); //megbízó számlaszám
				$elso .= $this->get_currency($this->own_data); //számla devizaneme
				$elso .= '1'; //megbízás típusa
				$elso .= '0'; //ügyfélprogram indikátor
				$elso .= Carbon::now()
				               ->format('Ymd'); //létrehozás dátuma
				$elso .= str_repeat(' ', 217) . "\n"; //fentartott
			}
			else
			{
				$elso .= '34'; //rekordtípus
				$elso .= $this->get_account_number($this->own_data); //megbízó számlaszám
				$elso .= $this->get_currency($this->own_data); //számla devizaneme
				$elso .= '1'; //megbízás típusa
				$elso .= '0'; //nem használt
				$elso .= Carbon::now()
				               ->format('Ymd'); //létrehozás dátuma
				$elso .= str_repeat(' ', 761) . "\n"; //fentartott
			}
			
			return $elso;
		}
		
		private function gen_data ($currency): string
		{
			$data = '';
			foreach ($this->transfers as $ind => $trans_lines)
			{
				if ($currency === 'HUF')
				{
					$data .= '43'; //rekortípus
					$data .= str_pad((string)($ind + 1), 6, '0', STR_PAD_LEFT); //rekord szám
					$data .= $this->get_account_number((object)$trans_lines); //kedvezményezett számlaszáma
					$data .= $this->get_partner_name((object)$trans_lines); // kedvezményezett neve
					$data .= '      ';//bizonylatszám
					$data .= str_pad(((object)$trans_lines)->notice, 96);//közlemény 1+2+3
					$data .= $this->get_osszeg((object)$trans_lines); //összeg
					$this->amountsum += $this->get_osszeg_num((object)$trans_lines);
					$data .= $this->get_currency($this->own_data);//deviza
					$data .= $this->get_datum((object)$trans_lines);//értéknap
					$data .= '            ';//jogcímkód, országkód és fenntartott
					$data .= '000000';//tranzakció típusa és hodozó
					$data .= str_repeat(' ', 46) . "\n"; //fentartott
					$this->recordsum += ($ind + 1);
					$this->accountsum = $this->sum_account_number($this->get_account_number((object)$trans_lines, '0'));
				}
				else
				{
					$data .= '54'; //rekortípus
					$data .= str_pad((string)($ind + 1), 6, '0', STR_PAD_LEFT); //rekord szám
					$data .= str_repeat(' ', 11); //kedvezményzett bank bic kód
					$data .= str_repeat(' ', 33); //kedvezményzett bank azonosító
					$data .= str_repeat(' ', 35); //kedvezményzett bank neve
					$data .= str_repeat(' ', 35); //kedvezményzett bank címe1
					$data .= str_repeat(' ', 35); //kedvezményzett bank címe2
					$data .= str_repeat(' ', 35); //kedvezményzett bank címe3
					$data .= str_repeat(' ', 11); //levelező bank bic kód
					$data .= str_repeat(' ', 33); //levelező bank azonosító
					$data .= str_repeat(' ', 35); //levelező bank neve
					$data .= str_repeat(' ', 35); //levelező bank címe1
					$data .= str_repeat(' ', 35); //levelező bank címe2
					$data .= str_repeat(' ', 35); //levelező bank címe3
					$data .= $this->get_iban_account_number((object)$trans_lines); //kedvezményezett IBAN számlaszáma
					$data .= $this->get_partner_name((object)$trans_lines,35); // kedvezményezett neve
					$data .= str_repeat(' ', 35); //kedvezményezett címe1
					$data .= str_repeat(' ', 35); //kedvezményezett címe2
					$data .= str_repeat(' ', 35); //kedvezményezett címe3
					$data .= str_pad(((object)$trans_lines)->notice, 140);//közlemény 1+2+3+4
					$data .= $this->get_currency($this->own_data);//teljesítés deviza
					$data .= $this->get_osszeg((object)$trans_lines); //összeg
					$this->amountsum += $this->get_osszeg_num((object)$trans_lines);
					$data .= $this->get_currency($this->own_data);//összeg deviza
					$data .= $this->get_datum((object)$trans_lines);//értéknap
					$data .= 'N';//Sürgősségi
					$data .= '0000';//Unicredit végrehajtói kódszám
					$data .= 'N';//HOLD flag
					$data .= 'N';//CHQB flag
					$data .= 'N';//Deal Ticket flag
					$data .= str_repeat('0', 8); //Deal Ticket dátum
					$data .= str_repeat('0', 6); //Deal Ticket sorszám
					$data .= str_repeat(' ', 2); //kedvezményezett órszágkódja
					$data .= '000';//Statisztikai jogcím kód
					$data .= '000';//Tranzakció típusa
					$data .= '000';//Tranzakció horodzó
					$data .= str_repeat(' ', 45) . "\n"; //fentartott
					$this->recordsum += ($ind + 1);
				}
			}
			
			return $data;
		}
		
		private function gen_trailer ($currency): string
		{
			if ($currency === 'HUF')
			{
				$trailer = '';
				$trailer .= '63'; //rekortípus
				$trailer .= str_pad((string)$this->recordsum, 6, '0');
				$trailer .= substr($this->accountsum, strlen($this->accountsum) - 24, 24);
				$trailer .= str_repeat(' ', 134);
				$trailer .= str_pad((string)$this->amountsum, 13, '0', STR_PAD_LEFT) . '00';
				$trailer .= str_repeat(' ', 75);
			}else{
				$trailer = '';
				$trailer .= '74'; //rekortípus
				$trailer .= str_pad((string)$this->recordsum, 6, '0');
				$trailer .= str_repeat(' ', 688);
				$trailer .= str_pad((string)$this->amountsum, 13, '0', STR_PAD_LEFT) . '00';
				$trailer .= str_repeat(' ', 89);
			}
			return $trailer;
		}
		
		private function get_account_number ($acc_data, $fill_char = ' '): string
		{
			if (property_exists($acc_data, 'account_number'))
			{
				$szlaszam = $acc_data->account_number;
			}
			else
			{
				$szlaszam = '000000000000000000000000';
			}
			$szlaszam = str_replace('-', '', $szlaszam);
			
			return strlen($szlaszam) < 24 ? str_pad($szlaszam, 24, $fill_char, STR_PAD_RIGHT) : $szlaszam;
		}
		
		private function get_iban_account_number ($acc_data, $fill_char = ' '): string
		{
			if (property_exists($acc_data, 'iban_account_number'))
			{
				if($this->checkIBAN($acc_data->iban_account_number))
				{$szlaszam = $acc_data->iban_account_number;}else{
					$szlaszam = '000000000000000000000000';
				}
			}
			else
			{
				$szlaszam = '000000000000000000000000';
			}
			
			return str_pad($szlaszam, 34, $fill_char, STR_PAD_RIGHT);
		}
		
		private function checkIBAN($iban) {
			
			// Normalize input (remove spaces and make upcase)
			$iban = strtoupper(str_replace(' ', '', $iban));
			
			if (preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $iban)) {
				$country = substr($iban, 0, 2);
				$check = (int)substr($iban, 2, 2);
				$account = substr($iban, 4);
				
				// To numeric representation
				$search = range('A','Z');
				foreach (range(10,35) as $tmp)
					$replace[]= (string)$tmp;
				$numstr=str_replace($search, $replace, $account.$country.'00');
				
				// Calculate checksum
				$checksum = (int)$numstr[0];
				for ($pos = 1; $pos < strlen($numstr); $pos++) {
					$checksum *= 10;
					$checksum += (int)$numstr[$pos];
					$checksum %= 97;
				}
				
				return ((98-$checksum) == $check);
			} else
				return false;
		}
		
		private function get_currency ($acc_data): string
		{
			if (property_exists($this->own_data, 'currency'))
			{
				return $this->own_data->currency;
			}
			
			return 'HUF';
		}
		
		private function get_partner_name ($acc_data,$length=32): string
		{
			if (property_exists($acc_data, 'partner_name'))
			{
				return str_pad($acc_data->partner_name, $length);
			}
			
			return str_pad('nincspartner', $length);
		}
		
		private function get_osszeg ($acc_data): string
		{
			if (property_exists($acc_data, 'amount'))
			{
				
				$osszeg = str_replace([
					                      '.',
					                      ',',
				                      ], '', $acc_data->amount);
				
				return str_pad($osszeg, 13, '0', STR_PAD_LEFT) . '00';
			}
			
			return str_pad('0', 13, '0', STR_PAD_LEFT) . '00';
		}
		
		private function get_osszeg_num ($acc_data): int
		{
			if (property_exists($acc_data, 'amount'))
			{
				
				$osszeg = str_replace([
					                      '.',
					                      ',',
				                      ], '', $acc_data->amount);
				
				return (int)$osszeg;
			}
			
			return 0;
		}
		
		private function get_datum ($acc_data): string
		{
			if (property_exists($acc_data, 'date'))
			{
				return str_replace('-', '', $acc_data->date);
			}
			
			return str_pad('0', 8, '0', STR_PAD_LEFT);
		}
		
		private function sum_account_number ($account_number): string
		{
			$account_number = str_pad($account_number, 24, '0', STR_PAD_RIGHT);
			$alap = str_split($this->accountsum, 8);
			$plus = str_split($account_number, 8);
			$harom = (int)$alap[2] + (int)$plus[2];
			$ketto = (int)$alap[1] + (int)$plus[1];
			$egy = (int)$alap[0] + (int)$plus[0];
			if ($harom > 99999999)
			{
				$haromelso = substr((string)$harom, 0, 1);
				$haromveg = substr((string)$harom, -8);
				if (strlen($haromelso) > 0)
				{
					$ketto += (int)$haromelso;
				}
			}
			else
			{
				$haromveg = str_pad((string)$harom, 8, '0', STR_PAD_LEFT);
			}
			if ($ketto > 99999999)
			{
				$kettoelso = substr((string)$harom, 0, 1);
				$kettoveg = substr((string)$harom, -8);
				if (strlen($kettoelso) > 0)
				{
					$egy += (int)$kettoelso;
				}
			}
			else
			{
				$kettoveg = str_pad((string)$ketto, 8, '0', STR_PAD_LEFT);
			}
			if ($egy > 99999999)
			{
				$egyveg = substr((string)$harom, -8);
			}
			else
			{
				$egyveg = str_pad((string)$egy, 8, '0', STR_PAD_LEFT);
			}
			
			return $egyveg . $kettoveg . $haromveg;
		}
	}
