<?php

class TunnelForDump{

	protected $opt = array(
		'ssh_host'=>'193.107.238.16',
		'ssh_port'=>22,
		'ssh_user'=>'u22481',
		'ssh_pass'=>'lufup1melup',
		'db_user' =>'biznesurfo',
		'db_pass' =>'wrbU3n77tY7CSrnd',
		'db_name' =>'biznesurfo_test',
		'db_charset' =>'cp1251',
		'local_path' =>'../extensions/ssh2_tunnel/tmp_dump/',
		'remote_path'=>'biznesurfo.ru/extensions/ssh2_tunnel/tmp_dump/',
	);

	protected $sql_head;
	protected $error;
	protected $err_number;

	public function goSQL($querry){
		$rand = date('YmdHis');
		$name_local  = 'dump_local_'.$rand.'.sql';
		$name_remote = 'dump_remote_'.$rand.'.sql';

		if (function_exists(ssh2_connect)){
			if ($fop = @fopen($this->opt['local_path'].$name_local, "w+")){
				$string = $this->sql_head.$querry;
				fwrite($fop, $string);
				fclose($fop);
				if ($con = ssh2_connect($this->opt['ssh_host'], $this->opt['ssh_port'])){
					if (ssh2_auth_password($con, $this->opt['ssh_user'], $this->opt['ssh_pass'])){
						if (ssh2_scp_send($con, $this->opt['local_path'].$name_local, $this->opt['remote_path'].$name_remote)){
							if (ssh2_exec($con, 'mysql --user='.$this->opt['db_user'].' --password='.$this->opt['db_pass'].' -B '.$this->opt['db_name'].' < '.$this->opt['remote_path'].$name_remote)){
								if (ssh2_exec($con, 'rm '.$this->opt['remote_path'].$name_remote)){
									$this->err_number = 0;
								}else{
									$this->error = 'Ошибка удаления файла на удаленном сервере';
									$this->err_number = 600;
								}
							}else{
								$this->error = 'Ошибка выполнения файла на удаленном сервере';
								$this->err_number = 500;
							}
						}else{
							$this->error = 'Ошибка при копировании файла, проверте путь или права доступа';
							$this->err_number = 400;
						}
					}else{
						$this->error = 'Ошибка авторизации пользователя, проверте логин и пароль';
						$this->err_number = 300;
					}
				}else{
					$this->error = 'Ошибка создания тунеля, проверте хост и порт';
					$this->err_number = 200;
				}
				if (file_exists($this->opt['local_path'].$name_local)) unlink ($this->opt['local_path'].$name_local);
			}else{
				$this->error = 'Ошибка создания локального файла';
				$this->err_number = 100;
			}
		}else{
			$this->error = 'Функции SSH2 не потдерживаются';
			$this->err_number = 700;
		}

		if ($this->err_number === 0) return true;
		else return false;
	}

	public function getError(){
		return $this->error.' : ['.$this->err_number.']';
	}

	public function __construct(){
		$this->sql_head = 'SET NAMES "'.$this->opt['db_charset'].'";'."\r\n";
		$this->error = 'Ошибок нет';
		$this->err_number = 0;
	}

}

?>