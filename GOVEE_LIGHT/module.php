<?php

declare(strict_types=1);


	class GOVEE_LIGHT extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();

			$this->RequireParent('{82347F20-F541-41E1-AC5B-A636FD3AE2D8}');

			$this->RegisterPropertyBoolean('Active', false);
			$this->RegisterPropertyInteger('Interval', 10);

			$this->RegisterVariableBoolean ("State", "State",  "~Switch", 10) ;
			$this->RegisterVariableInteger('Brightness', 'Brightness', '~Intensity.100', 20);
			$this->RegisterVariableInteger('Color', 'Color', '~HexColor', 30);
			//$this->RegisterVariableInteger('ColorTemperature', 'Color Temperature', 'Govee.ColorTemperature', 0);
        	$this->RegisterVariableInteger('ColorTemperature', 'Color Temperature', '', 40);
            
			$this->EnableAction('State');
			$this->EnableAction('Brightness');
			$this->EnableAction('Color');
			$this->EnableAction('ColorTemperature');
			
       
			
			$this->RegisterPropertyInteger("UpdateInterval", 10);
			//$this->RegisterPropertyString("IPAddress", "192.168.178.1");

			$this->RegisterTimer("Updatestate", ($this->ReadPropertyInteger("Interval"))*1000, 'GVL_UpdateState(' . $this->InstanceID . ');');
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

			parent::ApplyChanges();
            if ($this->ReadPropertyBoolean('Active')) {
                $this->SetTimerInterval('Updatestate', $this->ReadPropertyInteger('Interval') * 1000);
                $this->SetStatus(102);
            } else {
                $this->SetTimerInterval('Updatestate', 0);
                $this->SetStatus(104);
            }	
			
			
			$data = json_decode( IPS_GetConfiguration(IPS_GetInstance($this->InstanceID)["ConnectionID"] ), true);
			$this->SetSummary($data["Host"]);
			
		}

		public function SendData(string $Payload)
		{
			if ($this->HasActiveParent()) 
			{
                $this->SendDataToParent(json_encode(['DataID' => '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}', 'Buffer' => $Payload]));
            }
		}


		public function ReceiveData($JSONString)
        {
		
			//$data = json_decode($JSONString);
        	//IPS_LogMessage('Device RECV', utf8_decode($data->Buffer . ' - ' . $data->ClientIP . ' - ' . $data->ClientPort));
		
			$data = json_decode($JSONString, true);
			$buffer = json_decode($data['Buffer'], true);
            $deviceData = $buffer['msg']['data'];

            $this->SetValue('State', $deviceData['onOff']);
            $this->SetValue('Brightness', $deviceData['brightness']);

			$r =  $deviceData['color']['r'];
			$g =  $deviceData['color']['g'];
			$b =  $deviceData['color']['b'];
	
			$color = (int) ( ($r * 256 * 256) + ($g * 256) + $b);

            $this->SetValue('Color', $color);
            $this->SetValue('ColorTemperature', $deviceData['colorTemInKelvin']);
        }
		
		public function RequestAction($Ident, $Value)
        {
            switch ($Ident) {
                case 'State':
					$this->setState($Value);
					break;
                case 'Brightness':
                    $this->setBrightness($Value);
                    break;
                case 'Color':
                    $this->setColor($Value);
                    break;
                case 'ColorTemperature':
					if (!is_int($Value))
					{
						$Value = intval($Value);
					}
                    $this->setColorTemperature($Value);
                    break;
                default:
                    $this->SendDebug(__FUNCTION__, 'Invalid Action: ' . $Ident, 0);
                    break;
            }
        }


        public function UpdateState()
        {
			$this->SetTimerInterval("Updatestate", 0);
	
			$govee_message = '{ "msg" :{ "cmd" : "devStatus", "data" :{} }} ';
			$this->SendData($govee_message);

			$this->SetTimerInterval('Updatestate', $this->ReadPropertyInteger('Interval') * 1000);
		}



        private function setState(bool $state)
        {
			$value = (int)$state;
			$govee_message = '{ "msg" :{ "cmd" : "turn", "data" : { "value":' . $value . ' }}} ';
			
			$this->SendData($govee_message);
			$this->SetTimerInterval("Updatestate", 1000);
		}

        private function setBrightness(int $brightness)
        {
			if ($brightness > 0) 
			{
				if ($brightness < 100)
				{
					$value = $brightness;
				}
				else
				{
					$value = 100;
				}
			}
			else
			{
				$value = 0;
			}
			
			$govee_message = '{ "msg" :{ "cmd" : "brightness", "data" : { "value":' . $value . ' }}} ';
			$this->SendData($govee_message);
    		$this->SetTimerInterval("Updatestate", 1000);
	    }

        private function setColor(int $color)
        {
			$r = (int) ( ($color / 256 / 256) );
			$g = (int) ( ($color - ($r*256*256)) / 256 );
			$b = (int) ( ($color - ($r*256*256) - $g*256) ) ;
			
			$govee_message = '{ "msg" :{
										   "cmd":"colorwc", 
										   "data":  { 
												"color": { 
												  "r": '.  $r . ', 
												  "g": ' . $g . ', 
												  "b": ' . $b . '
												  }
												  , 
												  "colorTemInKelvin": ' . 0 . '
												}
											}
										}' ;
			
			$this->SendData($govee_message);
    		$this->SetTimerInterval("Updatestate", 1000);
	    }

        private function setColorTemperature(int $ct)
        {
			$govee_message = '{ "msg" :{
											"cmd":"colorwc", 
											"data":  { 
					 							"color": { 
					   							"r": ' . 0 . ', 
					   							"g": ' . 0 . ', 
					   							"b": ' . 0 . '
					   							}
					   							, 
					   							"colorTemInKelvin": ' . $ct . '
					 							}
				 							}
			 							}';

			 $this->SendData($govee_message);
			 $this->SetTimerInterval("Updatestate", 1000);
        }
	}