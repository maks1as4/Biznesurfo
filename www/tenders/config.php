<?php
$charset   = 'windows-1251';             # HTML - ( : windows-1251, koi8-r, utf-8  .) 
$host_name = 'www.biznesurfo.ru';  # mysite.ru,  www   
//$b2bcontext_path = NULL,                 # 
//$path = NULL,                            # url � �������� � ������������� /js � /css
$script_name  = '/tenders/main'; // $_SERVER['SCRIPT_NAME'];  # ����� ��������, �� ������� ����������� ������     
//$cache_path = NULL,                    # ���� �����������
$use_cache = 1;                        # ������������� ����������� 1 => ��, 0 =>���      
$update_time = '05:15';                  # ����� �������
$template_var = 'b2bcontext-style4';   # ����� � ��������

//$is_state   = NULL;                  # ��������: 1 - ������ ��������������� �������, 0 - ������ ������������, NULL - � �� � ������   

#-- ��������� �����������
$rubricator_additional_tender_cnt = 3; 
$rubricator_otr_str               = '';
$rubricator_show_subotrs          = 0;
    
#-- ��������� ������� ��������
$hot_tender_number = 3;
$hot_tender_keywords = '';
$hot_tender_otr = '';  
    
#-- ��������� ����� "������� � ���������" 
$get_digest_cnt = 3;
    
#-- ��������� gettenderlist
$gettenderlist_per_page = 20;

#-- ��������� ����������
$b2bcontext_server = 'http://b2bcontext.ru';
$cache_clear_interval=0;
$prefix="";
?>
