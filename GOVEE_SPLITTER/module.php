<?php

declare(strict_types=1);
	class GOVEE_SPLITTER extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();

			//$this->ConnectParent('{82347F20-F541-41E1-AC5B-A636FD3AE2D8}');
			$this->ForceParent('{82347F20-F541-41E1-AC5B-A636FD3AE2D8}');
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

			$this->GetConfigurationForParent();

			//print_r(IPS_GetConfiguration(29207));


		}

		public function GetConfigurationForParent()
        {
            $settings = [
                'BindPort'           => 4003,
				//'BindIP'           => '0.0.0.0',
                'EnableBroadcast'    => false,
                'EnableReuseAddress' => false,
                'Host'               => '',
                'Port'               => 4002,
				"Open"				=> true
            ];

            return json_encode($settings, JSON_UNESCAPED_SLASHES);

			//{"BindIP":"192.168.3.44","BindPort":4003,"EnableBroadcast":false,"EnableReuseAddress":false,"Host":"","Open":true,"Port":0}
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
		
			$data = json_decode($JSONString);
			//IPS_LogMessage('Splitter RECV', utf8_decode($data->Buffer . ' - ' . $data->ClientIP . ' - ' . $data->ClientPort));

			$this->SendDataToChildren(json_encode(['DataID' => '{1EF4729A-A536-49DC-57F5-6DB8E2E723A2}', 
			'Buffer' => $data->Buffer, 'ClientIP' => $data->ClientIP, 'ClientPort' => $data->ClientPort]));
		}
	}