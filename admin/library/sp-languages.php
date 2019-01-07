<?php
/*
Simple:Press
Desc: Supported Languages (from Glotpress)
$LastChangedDate: 2014-05-24 09:12:47 +0100 (Sat, 24 May 2014) $
$Rev: 11461 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------
# Array of supported languages from
# GlotPress site - used for upgrades
# and install
# ---------------------------------------

$langSets = array(
	'en'	=> array('wpCode' => 'en_US',	'langName' => 'English (USA)'),
	'en-gb'	=> array('wpCode' => 'en_GB',	'langName' => 'English (UK)'),
	'af'	=> array('wpCode' => 'af',		'langName' => 'Afrikaans'),
	'sq'	=> array('wpCode' => 'sq',		'langName' => 'Albanian'),
	'ar'	=> array('wpCode' => 'ar',		'langName' => 'Arabic'),
	'hy'	=> array('wpCode' => 'hy',		'langName' => 'Armenian'),
	'az'	=> array('wpCode' => 'az',		'langName' => 'Azerbaijani'),
	'be'	=> array('wpCode' => 'bel',		'langName' => 'Belarusian'),
	'bn'	=> array('wpCode' => 'bn_BD',	'langName' => 'Bengali'),
	'bs'	=> array('wpCode' => 'bs_BA',	'langName' => 'Bosnian'),
	'bg'	=> array('wpCode' => 'bg_BG',	'langName' => 'Bulgarian'),
	'ca'	=> array('wpCode' => 'ca',		'langName' => 'Catalan'),
	'bal'	=> array('wpCode' => 'bal',		'langName' => 'Catalan (Balear)'),
	'zh-cn'	=> array('wpCode' => 'zh_CN',	'langName' => 'Chinese (China)'),
	'zh-hk'	=> array('wpCode' => 'zh_HK',	'langName' => 'Chinese (Hong Kong)'),
	'zh-tw'	=> array('wpCode' => 'zh_TW',	'langName' => 'Chinese (Taiwan)'),
	'hr'	=> array('wpCode' => 'hr',		'langName' => 'Croatian'),
	'cs'	=> array('wpCode' => 'cs_CZ',	'langName' => 'Czech'),
	'da'	=> array('wpCode' => 'da_DK',	'langName' => 'Danish'),
	'nl'	=> array('wpCode' => 'nl_NL',	'langName' => 'Dutch'),
	'et'	=> array('wpCode' => 'et',		'langName' => 'Estonian'),
	'fo'	=> array('wpCode' => 'fo',		'langName' => 'Faroese'),
	'fi'	=> array('wpCode' => 'fi',		'langName' => 'Finnish'),
	'fr-ca'	=> array('wpCode' => 'fr_CA',	'langName' => 'French (Canada)'),
	'fr'	=> array('wpCode' => 'fr_FR',	'langName' => 'French (France)'),
	'ka'	=> array('wpCode' => 'ka_GE',	'langName' => 'Georgian'),
	'de'	=> array('wpCode' => 'de_DE',	'langName' => 'German'),
	'el'	=> array('wpCode' => 'el',		'langName' => 'Greek'),
	'gu'	=> array('wpCode' => 'gu',		'langName' => 'Gujarati'),
	'he'	=> array('wpCode' => 'he_IL',	'langName' => 'Hebrew'),
	'hi'	=> array('wpCode' => 'hi_IN',	'langName' => 'Hindi'),
	'hu'	=> array('wpCode' => 'hu_HU',	'langName' => 'Hungarian'),
	'is'	=> array('wpCode' => 'is_IS',	'langName' => 'Icelandic'),
	'id'	=> array('wpCode' => 'id_ID',	'langName' => 'Indonesian'),
	'it'	=> array('wpCode' => 'it_IT',	'langName' => 'Italian'),
	'ja'	=> array('wpCode' => 'ja',		'langName' => 'Japanese'),
	'ko'	=> array('wpCode' => 'ko_KR',	'langName' => 'Korean'),
	'la'	=> array('wpCode' => 'la',		'langName' => 'Latin'),
	'lv'	=> array('wpCode' => 'lv',		'langName' => 'Latvian'),
	'lt'	=> array('wpCode' => 'li',		'langName' => 'Lithuanian'),
	'mk'	=> array('wpCode' => 'mk_MK',	'langName' => 'Macedonian'),
	'no'	=> array('wpCode' => 'nn_NO',	'langName' => 'Norwegian'),
	'nb'	=> array('wpCode' => 'nb_NO',	'langName' => 'Norwegian (Bokmål)'),
	'fa'	=> array('wpCode' => 'fa_IR',	'langName' => 'Persian (Iran)'),
	'pl'	=> array('wpCode' => 'pl_PL',	'langName' => 'Polish'),
	'pt-br'	=> array('wpCode' => 'pt_BR',	'langName' => 'Portuguese (Brazil)'),
	'pt'	=> array('wpCode' => 'pt_PT',	'langName' => 'Portuguese (Portugal)'),
	'ro'	=> array('wpCode' => 'ro_RO',	'langName' => 'Romanian'),
	'ru'	=> array('wpCode' => 'ru_RU',	'langName' => 'Russian'),
	'sr'	=> array('wpCode' => 'sr_RS',	'langName' => 'Serbian'),
	'sk'	=> array('wpCode' => 'sk_SK',	'langName' => 'Slovak'),
	'sl'	=> array('wpCode' => 'sl_SL',	'langName' => 'Slovenian'),
	'es'	=> array('wpCode' => 'es_ES',	'langName' => 'Spanish (Spain)'),
	'sv'	=> array('wpCode' => 'sv_SE',	'langName' => 'Swedish'),
	'tl'	=> array('wpCode' => 'tl',		'langName' => 'Tagalog'),
	'ta-lk'	=> array('wpCode' => 'ta_LK',	'langName' => 'Tamil (Sri Lanka)'),
	'th'	=> array('wpCode' => 'th',		'langName' => 'Thai'),
	'tr'	=> array('wpCode' => 'tr_TR',	'langName' => 'Turkish'),
	'uk'	=> array('wpCode' => 'uk',		'langName' => 'Ukrainian'),
	'uz'	=> array('wpCode' => 'uz_UZ',	'langName' => 'Uzbek'),
	'vi'	=> array('wpCode' => 'vi',		'langName' => 'Vietnamese')
);
