Oto szczegółowy flow działania sieci afiliacyjnej w platformie HelpDreams – krok po kroku dla frontu
i backendu, wraz z instrukcją tworzenia linków afiliacyjnych.


1. Przegląd architektury

Platforma teraz obsługuje dwa modele finansowania marzeń:

 • Darowizny bezpośrednie (istniejący model) – darczyńca wpłaca pieniądze, dyrektor potwierdza
   otrzymanie prezentu (DreamFulfillment z type = 'donation').
 • Sieć afiliacyjna (nowy model) – użytkownik klika link afiliacyjny, kupuje produkt w zewnętrznym
   sklepie, a my śledzimy kliknięcia i konwersje (AffiliateClick, AffiliateConversion). Prowizja z
   zakupu trafia do programu partnerskiego, a my rejestrujemy zakup jako część spełnienia marzenia
   (Dream::purchasedQuantity).


2. Flow dla dyrektora (tworzenie marzenia z linkiem afiliacyjnym)

Krok 1 – Logowanie jako dyrektor (ROLE_DIRECTOR)

 • Dyrektor loguje się i przechodzi do panelu dyrektora (/director).

Krok 2 – Dodanie nowego marzenia

 • Kliknięcie "Dodaj nowe marzenie" prowadzi do formularza DreamType.
 • Nowe pola w formularzu:
    • Oryginalny link produktu (afiliacyjny) – bezpośredni URL produktu w sklepie partnerskim (np.
      https://allegro.pl/rower-gorski-24-cale).
    • Partner afiliacyjny – dropdown z wyborem: Ceneo, Amazon, Allegro, Inny, Brak.
    • ID śledzenia afiliacyjnego – unikalny kod z programu partnerskiego (np. helpdreams123). Jeśli
      puste, system użyje domyślnego.
    • Wygenerowany link afiliacyjny – pole opcjonalne; system może je automatycznie wypełnić po
      zapisie.
 • Dyrektor wypełnia również standardowe pola (tytuł, cena, dziecko, ilość potrzebna itp.).

Krok 3 – Zapis marzenia

 • Po zatwierdzeniu formularza wywoływany jest DreamAffiliateSubscriber (event Doctrine prePersist).
 • Subscriber wywołuje AffiliateLinkGenerator::updateDreamAffiliateUrl():
    • Generator sprawdza originalProductUrl i affiliatePartner.
    • Na podstawie partnera dodaje odpowiednie parametry śledzące do URL:
       • Allegro: ?aff_id=TRACKING_ID
       • Ceneo: ?pid=TRACKING_ID
       • Amazon: ?tag=TRACKING_ID
       • Inny: jeśli podano affiliateTrackingId, dodaje ?aff_id=TRACKING_ID
    • Wygenerowany link jest zapisywany w Dream::affiliateUrl.
 • Marzenie trafia do bazy z statusem pending (lub verified w zależności od ustawień).

Krok 4 – Weryfikacja przez administratora (opcjonalnie)

 • Administrator w panelu (/admin) może zweryfikować marzenie i zmienić status na verified.


3. Flow dla użytkownika (przeglądanie i zakup przez afiliację)

Krok 1 – Przeglądanie listy marzeń

 • Użytkownik (anonimowy lub zalogowany) odwiedza /dreams.
 • W widoku listy (szablon dream/index.html.twig) marzenia, które mają affiliateUrl, mogą być
   oznaczone ikoną 🔗 (do implementacji).
 • Kliknięcie na marzenie prowadzi do strony szczegółów (/dreams/{id}).

Krok 2 – Strona szczegółów marzenia

 • Szablon dream/show.html.twig wyświetla:
    • Sekcję "Kup przez afiliację" (jeśli affiliateUrl jest ustawiony) z dużym przyciskiem "Kup teraz
      (link afiliacyjny)".
    • Statystyki afiliacyjne: liczba kliknięć, liczba zakupionych sztuk, partner.
    • Tradycyjną sekcję darowizn z przyciskiem "Chcę pomóc (darowizna)".
 • Przycisk "Kup teraz" linkuje do affiliate_redirect (/go/{id}).

Krok 3 – Śledzenie kliknięcia (AffiliateController::redirectAction)

 • Endpoint /go/{id}:
    1 Pobiera marzenie (Dream).
    2 Tworzy nowy rekord AffiliateClick z danymi: IP, user‑agent, session ID, timestamp.
    3 Zapisuje kliknięcie w bazie.
    4 Przekierowuje użytkownika (HTTP 302) na Dream::affiliateUrl (lub productUrl, jeśli afiliacyjny
      brak).
 • Uwaga: przekierowanie jest natychmiastowe, użytkownik trafia do sklepu partnerskiego.

Krok 4 – Zakup w sklepie partnerskim

 • Użytkownik dokonuje zakupu w sklepie (Allegro, Ceneo, Amazon itp.) korzystając z linku
   zawierającego nasz kod śledzący.
 • Program partnerski sklepu rejestruje transakcję i przypisuje ją do naszego konta (poza platformą).

Krok 5 – Rejestracja konwersji (zakupu) w systemie

 • Opcja A – Ręczne dodanie przez administratora/dyrektora:
    • Administrator lub dyrektor (jeśli marzenie należy do jego domu dziecka) loguje się do panelu.
    • W panelu administratora (/admin/affiliate) wybiera marzenie i klika "Dodaj konwersję".
    • Wypełnia formularz: numer zamówienia (opcjonalnie), kwota, prowizja, ilość sztuk.
    • Po zapisie tworzony jest rekord AffiliateConversion, a Dream::purchasedQuantity jest
      automatycznie przeliczane (via DreamRepository::updatePurchasedQuantity).
 • Opcja B – Automatyczny webhook (future): partner może wysłać POST na nasz endpoint z danymi
   transakcji; webhook tworzy AffiliateConversion.

Krok 6 – Aktualizacja stanu marzenia

 • Po każdej konwersji (ręcznej lub przez webhook) wywoływana jest metoda
   DreamRepository::updatePurchasedQuantity, która sumuje quantity z wszystkich AffiliateConversion
   dla danego marzenia i aktualizuje pole Dream::purchasedQuantity.
 • Logika spełnienia marzenia uwzględnia teraz dwie składowe:
    • Dream::quantityFulfilled – suma darowizn bezpośrednich (DreamFulfillment z type = 'donation').
    • Dream::purchasedQuantity – suma zakupów afiliacyjnych.
 • Marzenie jest oznaczone jako fulfilled, gdy quantityFulfilled + purchasedQuantity >=
   quantityNeeded.


4. Panel administratora – pełna kontrola

Dostęp: tylko użytkownicy z ROLE_ADMIN.

Ścieżki:

 • /admin/affiliate – dashboard ze statystykami wszystkich marzeń.
    • Tabela z kolumnami: ID marzenia, tytuł, partner, kliknięcia, zakupione sztuki, współczynnik
      konwersji, akcje.
    • Przycisk "Dodaj konwersję" dla każdego marzenia.
 • /admin/affiliate/conversion/add/{id} – formularz ręcznego dodania konwersji.
 • /admin/affiliate/conversion/{id}/delete – usunięcie konwersji (POST z tokenem CSRF).

Funkcjonalności:

 • Przegląd wszystkich kliknięć i konwersji (można rozszerzyć o szczegółowe listy).
 • Ręczna korekta danych (np. poprawa ilości, usunięcie błędnego wpisu).
 • Monitorowanie współczynnika konwersji (kliknięcia → zakupy) dla każdego marzenia.


5. Instrukcja tworzenia linków afiliacyjnych (dla administratora/dyrektora)

Krok 1 – Zarejestruj się w programach partnerskich

 • Załóż konta w programach partnerskich wybranych sklepów (Allegro – Allegro Partners, Ceneo –
   CeneoLab, Amazon – Amazon Associates).
 • Odbierz swój unikalny identyfikator śledzenia (np. helpdreams123).

Krok 2 – Przygotuj oryginalny link produktu

 • Wejdź na stronę produktu w sklepie (np. Allegro).
 • Skopiuj URL z paska adresu (np. https://allegro.pl/rower-gorski-24-cale).

Krok 3 – Wypełnij pola afiliacyjne w formularzu marzenia

 • Oryginalny link produktu: wklej skopiowany URL.
 • Partner afiliacyjny: wybierz odpowiedni sklep z listy.
 • ID śledzenia afiliacyjnego: wpisz identyfikator otrzymany z programu partnerskiego (możesz
   zostawić puste – system użyje domyślnego).
 • Wygenerowany link afiliacyjny: zostaw puste – system utworzy go automatycznie.

Krok 4 – Sprawdź wygenerowany link

 • Po zapisaniu marzenia przejdź do jego szczegółów.
 • W sekcji "Statystyki afiliacyjne" zobaczysz "Link afiliacyjny". Kliknij "Przejdź do sklepu", aby
   sprawdzić, czy przekierowanie działa poprawnie i czy URL zawiera Twój kod śledzący (np.
   ?aff_id=helpdreams123).


6. Flow danych w bazie – podsumowanie

 1 Dream – nowe pola:
    • affiliatePartner (string)
    • affiliateTrackingId (string)
    • originalProductUrl (text)
    • affiliateUrl (text)
    • purchasedQuantity (int)
 2 AffiliateClick – każdy klik na link afiliacyjny:
    • dream (relacja)
    • ipAddress, userAgent, sessionId
    • clickedAt (timestamp)
 3 AffiliateConversion – każdy zarejestrowany zakup:
    • dream (relacja)
    • click (opcjonalna relacja do kliknięcia)
    • orderId, amount, commission, quantity
    • convertedAt (timestamp)
 4 DreamFulfillment – rozszerzone o pole type (donation / affiliate). Na razie używane tylko dla
   darowizn bezpośrednich, ale pozwala na jednolitą historię spełnień.


7. Co jeszcze można dodać (future)

 • Automatyczne webhooki – integracja z API partnerów do automatycznego pobierania konwersji.
 • E‑mailowe powiadomienia o nowych kliknięciach/konwersjach dla dyrektora.
 • Zaawansowane statystyki – wykresy, eksport CSV.
 • Walidacja linków afiliacyjnych – sprawdzanie, czy URL jest poprawny i czy zawiera wymagane
   parametry.
 • Wsparcie wielu partnerów dla jednego marzenia – jeśli produkt jest dostępny w kilku sklepach.