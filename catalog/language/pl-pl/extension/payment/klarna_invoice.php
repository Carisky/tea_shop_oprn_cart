<?php
// Text
$_['text_title']				= 'Faktura Klarna - zapłać w ciągu 14 dni';
$_['text_terms_fee']			= '<span id="klarna_invoice_toc"></span> (+%s)<script type="text/javascript">var terms = new Klarna.Terms.Invoice({el: \'klarna_invoice_toc\', eid: \'%s\', country: \'%s\', charge: %s});</script>';
$_['text_terms_no_fee']			= '<span id="klarna_invoice_toc"></span><script type="text/javascript">var terms = new Klarna.Terms.Invoice({el: \'klarna_invoice_toc\', eid: \'%s\', country: \'%s\'});</script>';
$_['text_additional']			= 'Klarna Invoice requires some additional information before they can proccess your order.';
$_['text_male']					= 'Mężczyzna';
$_['text_female']				= 'Kobieta';
$_['text_year']					= 'Rok';
$_['text_month']				= 'Miesiąc';
$_['text_day']					= 'Dzień';
$_['text_comment']				= 'ID faktury Klarna: %s. ' . "\n" . '%s/%s: %.4f';

// Entry
$_['entry_gender']				= 'Płeć';
$_['entry_pno']					= 'Numer osobisty';
$_['entry_dob']					= 'Data urodzenia';
$_['entry_phone_no']			= 'Numer telefonu';
$_['entry_street']				= 'Ulica';
$_['entry_house_no']			= 'Nr domu.';
$_['entry_house_ext']			= 'Numer domu.';
$_['entry_company']				= 'Numer rejestracyjny firmy';

// Help
$_['help_pno']					= 'Wpisz tutaj swój numer pesel.';
$_['help_phone_no']				= 'Wpisz swój numer telefonu.';
$_['help_street']				= 'Proszę pamiętać, że dostawy mogą być przyjęte na zarejestrowany adres przy płaceniu z Klarna.';
$_['help_house_no']				= 'Wpisz numer domu.';
$_['help_house_ext']			= 'Wpisz rozszerzenie swojego domu jeżeli jest. Na przykład A, B, C, Czerwony, Niebieski itp.';
$_['help_company']				= 'Wpisz numer rejestracyjny firmy';

// Error
$_['error_deu_terms']			= 'Musisz zaakceptować zasady ochrony prywatności Klarna\'s (Datenschutz)';
$_['error_address_match']		= 'Adres bilingowy i wysyłkowy musi się zgadzać, jeżeli chcesz użyć Klarna Invoice';
$_['error_network']				= 'Wystąpił błąd podczas łączenia z Klarna. Spróbuj ponownie później.';