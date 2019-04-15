<?php
$charset   = 'windows-1251';             # HTML - ( : windows-1251, koi8-r, utf-8  .) 
$host_name = 'www.biznesurfo.ru';  # mysite.ru,  www   
//$b2bcontext_path = NULL,                 # 
//$path = NULL,                            # url к каталогу с подкаталогами /js и /css
$script_name  = '/tenders/main'; // $_SERVER['SCRIPT_NAME'];  # Адрес страницы, на которой размещается парсер     
//$cache_path = NULL,                    # Путь кэширования
$use_cache = 1;                        # Использование кэширования 1 => да, 0 =>нет      
$update_time = '05:15';                  # Время апдейта
$template_var = 'b2bcontext-style4';   # Папка с шаблоном

//$is_state   = NULL;                  # Выводить: 1 - только государственные тендеры, 0 - только коммерческие, NULL - и те и другие   

#-- Настройки рубрикатора
$rubricator_additional_tender_cnt = 3; 
$rubricator_otr_str               = '';
$rubricator_show_subotrs          = 0;
    
#-- Настройки горячих тендеров
$hot_tender_number = 3;
$hot_tender_keywords = '';
$hot_tender_otr = '';  
    
#-- Настройки блока "новости и аналитика" 
$get_digest_cnt = 3;
    
#-- Настройки gettenderlist
$gettenderlist_per_page = 20;

#-- Системные переменные
$b2bcontext_server = 'http://b2bcontext.ru';
$cache_clear_interval=0;
$prefix="";
?>
