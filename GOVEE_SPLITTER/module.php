<?php

declare(strict_types=1);
	class GOVEE_SPLITTER extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();

			$this->ConnectParent('{82347F20-F541-41E1-AC5B-A636FD3AE2D8}');
		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
		}

		public function ForwardData($JSONString)
		{
			$data = json_decode($JSONString);
			//IPS_LogMessage('Splitter FRWD', utf8_decode($data->Buffer . ' - ' . $data->ClientIP . ' - ' . $data->ClientPort));

			$this->SendDataToParent(json_encode([
				//'DataID' => '{C8792760-65CF-4C53-B5C7-A30FCC84FEFE}', 
				//'Type' => 0, 
		
				'DataID' => '{8E4D9B23-E0F2-1E05-41D8-C21EA53B8706}', 

				'Buffer' => utf8_decode($data->Buffer), 
				
				'ClientIP' => $data->ClientIP,
            	'ClientPort' => $data->ClientPort,
				'Broadcast' => false

				]));

			return 'String data for device instance!';
		}

		public function ReceiveData($JSONString)
		{
		
			//$guid = "{01CE6F1F-772D-83C5-4AAC-194173A05117}";
			//print_r(IPS_GetInstanceListByModuleID($guid));

			//print_r(IPS_GetInstance(23736));
			//print_r(IPS_GetInstance(22198));

			$data = json_decode($JSONString);
			//IPS_LogMessage('Splitter RECV', utf8_decode($data->Buffer . ' - ' . $data->ClientIP . ' - ' . $data->ClientPort));

			$this->SendDataToChildren(json_encode(['DataID' => '{1EF4729A-A536-49DC-57F5-6DB8E2E723A2}', 
			'Buffer' => $data->Buffer, 'ClientIP' => $data->ClientIP, 'ClientPort' => $data->ClientPort]));
		}
	}