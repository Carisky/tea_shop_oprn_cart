<?php
// Heading
$_['heading_title']                     = 'Import towaru';
$_['text_openbay']                      = 'OpenBay Pro';
$_['text_ebay']                         = 'eBay';

// Text
$_['text_sync_import_line1']            = '<strong>Uwaga!</strong> To będzie importować wszystkie produkty eBay i budować strukturę kategorii w Twoim sklepie. Zaleca się, że usuniesz wszystkie kategorie i produkty przed uruchomieniem tej opcji. < br / &gt; Struktura kategorii jest z kategorii eBay normalne, nie Twój sklep kategorii (Jeśli masz Sklep eBay). Można zmienić nazwę, usunąć i edytować importowane kategorie bez wpływu na produkty eBay.';
$_['text_sync_import_line3']            = 'Musisz upewnić się że serwer może akceptować i procesować dane wielkiego rozmiaru. 1000 elementów eBay to około 40mb pamięci, będziesz musiał przeliczyć ile potrzebujesz. Jeśli twoje połączenie nie powiedzie się, to najprowdopodobniej z powodu zbyt małego ustawienia. Limit pamięci PHP musi być około 128MB.';
$_['text_sync_server_size']             = 'Obecnie twój serwer może zaakceptować: ';
$_['text_sync_memory_size']             = 'Limit pamięci PHP: ';
$_['text_import_confirm']				= 'To importuje wszystkie przedmioty z serwisu eBay jako nowe produkty, jesteś pewny? To NIE MOŻE być nieskończone! Upewnij się że posiadasz kopię zapasową!';
$_['text_import_notify']				= 'Twoje żądanie importu zostało wysłane do przetworzenia. Import zamuje około 1 godziny od 1000 sztuk.';
$_['text_import_images_msg1']           = 'obrazy są oczekujące importu/Kopiuj z eBay. Odśwież Tę stronę, jeśli numer nie zmniejsza się następnie';
$_['text_import_images_msg2']           = 'kliknij tutaj';
$_['text_import_images_msg3']           = 'i czekaj. Więcej informacji o tym, dlaczego to się stało można znaleźć <a href="http://shop.openbaypro.com/index.php?route=information/faq&topic=8_45" target="_blank">tutaj</a>';

// Entry
$_['entry_import_item_advanced']        = 'Korzystaj z zaawansowanych danych';
$_['entry_import_categories']         	= 'Importuj kategorie';
$_['entry_import_description']			= 'Importuj opis towarów';
$_['entry_import']						= 'Importuj przedmioty eBay';

// Buttons
$_['button_import']						= 'Importuj';

// Help
$_['help_import_item_advanced']        	= 'Potrwa do 10 razy dłużej aby importować przedmioty. Importowanie wagi, rozmiarów, ISBN i więcej jeżeli dostępne';
$_['help_import_categories']         	= 'Buduje struktury kategorii w Twoim sklepie z kategorii eBay';
$_['help_import_description']         	= 'To będzie importować wszystko włącznie z HTML, liczniki visit itp';

// Error
$_['error_import']                   	= 'Nie udało się załadować';
$_['error_maintenance']					= 'Twój sklep jest w trybie konserwacji. Import nie powiedzie się!';
$_['error_ajax_load']					= 'Nie można połączyć z serwerem';
$_['error_validation']					= 'Musisz zarejestrować swój token API i włączyć moduł.';