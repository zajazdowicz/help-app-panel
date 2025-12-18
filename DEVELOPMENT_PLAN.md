# Development Plan – Portal Pomocy dla Domów Dziecka

Ten dokument opisuje aktualny stan aplikacji, brakujące funkcjonalności, plan implementacji oraz uwagi dotyczące bezpieczeństwa.  
**Aktualizowany przy każdym wczytaniu projektu oraz po wprowadzeniu znaczących modyfikacji.**

*Ostatnia duża modyfikacja: dodanie pełnej implementacji sieci afiliacyjnej (Faza 13) – 2025-12-18.*

---

## 1. Przegląd istniejącego kodu

### Encje
- **User** – podstawowa encja użytkownika (email, username, roles, password, powiązanie z Orphanage).
- **Orphanage** – dom dziecka (dane adresowe, contact, flagi verified, relacje z Child, Dream oraz User (director)).
- **Child** – dziecko (imię, wiek, opis, verified, orphanage, dreams).
- **Dream** – marzenie dziecka (product details, status, quantity needed/fulfilled, urgent, orphanage, child).
- **DreamFulfillment** – darowizna na rzecz marzenia (dane darczyńcy, status, quantity, opcjonalne childPhotoUrl i childMessage).

### Kontrolery
- **HomeController** – strona główna.
- **RegistrationController** – rejestracja użytkownika.
- **SecurityController** – logowanie/wylogowanie.

### Repozytoria
- ChildRepository, DreamRepository, OrphanageRepository – podstawowe metody.

### Szablony
- base.html.twig, home/index.html.twig, registration/register.html.twig, security/login.html.twig.

### Bezpieczeństwo
- Konfiguracja SecurityBundle (domyślny firewall, hashowanie haseł).
- CSRF w formularzach FrameworkBundle.
- Walidacja przez ValidationBundle.

---

## 2. Brakujące funkcjonalności (na podstawie analizy)

### 2.1. Przeglądanie i zarządzanie marzeniami
- Publiczna lista marzeń z paginacją, filtrami (status, pilne, dom dziecka).
- Szczegóły pojedynczego marzenia.
- Formularz dodawania/edycji marzeń (dla dyrektora domu dziecka).
- Zmiana statusu marzenia (np. weryfikacja przez admina).

### 2.2. System darowizn (DreamFulfillment)
- Formularz złożenia darowizny (dla zalogowanych i anonimowych użytkowników).
- Panel podsumowania darowizn użytkownika (jeśli zalogowany).
- Zmiana statusu realizacji darowizny (np. potwierdzenie, wysłanie, dostarczenie).

### 2.3. Panel dyrektora domu dziecka
- Zarządzanie dziećmi (CRUD).
- Zarządzanie marzeniami dzieci.
- Przegląd darowizn dla marzeń z jego domu dziecka.

### 2.4. Panel administratora
- Weryfikacja domów dziecka (ustawienie flagi `isVerified`).
- Przegląd wszystkich użytkowników, dzieci, marzeń, darowizn.
- Możliwość zmiany ról użytkowników.

### 2.5. Usprawnienia encji
- **Dream::status** – warto użyć typu wyliczeniowego (enum) zamiast dowolnego stringa.
- **DreamFulfillment::status** – podobnie.
- **DreamFulfillment** – brak relacji z User (dla zalogowanych darczyńców).
- **User** – brak domyślnej roli przy rejestracji (np. `ROLE_USER`).
- Brak pola `photoUrl` w Child (opcjonalne zdjęcie dziecka).
- Brak pola `amount` w DreamFulfillment? (obecnie `quantityFulfilled` – ilość sztuk, ale może warto dodać kwotę darowizny).

### 2.6. Bezpieczeństwo i walidacja
- Ograniczenie dostępu do określonych ścieżek według ról (ROLE_USER, ROLE_DIRECTOR, ROLE_ADMIN).
- Walidacja danych wejściowych w formularzach (np. email, zakresy ilości).
- Zabezpieczenie przed XSS (Twig domyślnie escape’uje, ale trzeba uważać na pola HTML).
- Zabezpieczenie przed SQL injection (Doctrine używa parametryzowanych zapytań).

### 2.7. Testy
- Brak testów jednostkowych i funkcjonalnych.

### 2.8. Nowe wymagania biznesowe (Zamieszczanie i realizacja marzeń)
- **Formularz dodawania marzeń** przez dyrekcję domu dziecka z polami: link do produktu, cena, ilość potrzebna, dane dziecka (imię, wiek, opis do 100 znaków), dane placówki (adres, email, telefon – niepubliczne).
- **Weryfikacja przed publikacją** – domyślny status `pending`, wymagane zatwierdzenie przez admina lub dyrektora innego domu (mechanizm akceptacji).
- **Oznaczanie marzenia jako „w realizacji”** – automatyczne po złożeniu pierwszej darowizny lub ręczne przez dyrektora.
- **Częściowe spełnianie** – możliwość zadeklarowania ilości, którą darczyńca chce pokryć (pozostała ilość jest aktualizowana).
- **Potwierdzenie spełnienia** – po dostarczeniu prezentu dziecko/dyrekcja może dodać zdjęcie lub wiadomość podziękowania (wykorzystanie istniejących pól `childPhotoUrl` i `childMessage` w DreamFulfillment).
- **Sortowanie i filtrowanie** w publicznej liście: cena (rosnąco/malejąco), kategoria, region (poprzez dane placówki), status pilności.
- **Przekierowanie do sklepu** – po kliknięciu „Chcę pomóc” użytkownik widzi dane adresowe placówki oraz imię dziecka, aby mógł skopiować do zamówienia w zewnętrznym sklepie.
- **Statystyki wartości przekazanych darów** – suma `productPrice * quantityFulfilled` dla spełnionych marzeń; widżet ujawniany po przekroczeniu progu (np. 2000 zł).
- **Obsługa reklam** – dedykowane miejsce w szablonie na banery Google Ads lub sponsorów.

---

## 3. Plan implementacji (kolejność priorytetowa)

### Faza 1 – Uzupełnienie encji i podstawowych zabezpieczeń
1. **Dodanie relacji DreamFulfillment → User** (nullable, dla zalogowanych darczyńców).
2. **Ustawienie domyślnej roli w konstruktorze User** (`$this->roles = ['ROLE_USER'];`).
3. **Stworzenie migracji** dla nowych pól/relacji.
4. **Aktualizacja konfiguracji ról w security.yaml** (hierarchy: ROLE_USER, ROLE_DIRECTOR, ROLE_ADMIN).

### Faza 2 – Publiczna lista marzeń i szczegóły ✅
1. ✅ **Utworzenie DreamController** z akcjami:
   - `public function index(Request $request): Response` (lista z paginacją i filtrami)
   - `public function show(Dream $dream): Response`
2. ✅ **Rozszerzenie DreamRepository** o metody wyszukiwania z filtrami.
3. ✅ **Szablony** `templates/dream/index.html.twig`, `templates/dream/show.html.twig`.

### Faza 3 – Formularz darowizny ✅
1. ✅ **Utworzenie DreamFulfillmentController** z akcją `public function fulfill(Request $request, Dream $dream): Response`.
2. ✅ **Stworzenie DreamFulfillmentType** (formularz z danymi darczyńcy i ilością).
3. ✅ **Obsługa zapisu** wraz z aktualizacją `Dream::quantityFulfilled`.
4. ✅ **Szablon** `templates/dream_fulfillment/fulfill.html.twig`.

### Faza 4 – Panel dyrektora (ROLE_DIRECTOR) – ✅ UKOŃCZONA
1. ✅ **Stworzenie ChildController** (CRUD dla dzieci, dostęp tylko dla director swojego domu dziecka).
2. ✅ **Stworzenie DreamController akcje `new`, `edit`, `delete`** (z ograniczeniem do własnego orphanage).
3. ✅ **Szablony** dla zarządzania dzieci (index, new, edit) i marzeń (new, edit).
4. ✅ **Formularze** ChildType i DreamType (z ograniczeniem dzieci do własnego domu dziecka).
5. ✅ **Link w navbarze** do panelu dyrektora.
6. ✅ **Integracja z bazą danych** – formularz DreamType automatycznie filtruje dzieci należące do domu dziecka dyrektora.
7. ✅ **Przyciski edycji/usuwania marzeń** w widoku szczegółów marzenia dla dyrektora.
8. ✅ **Linki do dodawania nowego marzenia** w panelu dyrektora i widoku szczegółów.
9. ✅ **Logika biznesowa** – dyrektor dodaje marzenia w imieniu dzieci ze swojego domu dziecka, wybierając konkretne dziecko z listy.
10. ✅ **Widok listy marzeń dla dyrektora** – z filtrowaniem po statusie, dostępny pod `/dreams/director/list`.
11. ✅ **Inteligentne linki powrotu** – dyrektor w widokach edycji/dodawania/szczegółów marzenia jest kierowany do swojej listy marzeń, a nie do publicznej listy.

### Faza 5 – Panel administratora (ROLE_ADMIN) – ✅ UKOŃCZONA
1. ✅ **Utworzenie AdminController** z akcjami:
   - Dashboard ze statystykami
   - Lista użytkowników z możliwością zmiany ról
   - Lista domów dziecka do weryfikacji
   - Lista wszystkich marzeń z możliwością zmiany statusu
   - Lista wszystkich darowizn
2. ✅ **Szablony** administracyjne (dashboard, users, orphanages, dreams, fulfillments)
3. ✅ **Link w navbarze** do panelu administratora
4. ✅ **Funkcjonalność zmiany ról użytkowników**
5. ✅ **Funkcjonalność weryfikacji domów dziecka**
6. ✅ **Funkcjonalność zmiany statusu marzeń**

### Faza 6 – Usprawnienia i bezpieczeństwo – W TRAKCIE
1. ✅ **Przyjazna strona główna** – nowy szablon z statystykami, ostatnimi marzeniami i sekcją "Jak to działa".
2. ✅ **Aktualizacja HomeController** – pobieranie statystyk i ostatnich marzeń.
3. ✅ **Instalacja komponentu Symfony Asset** – umożliwia korzystanie z funkcji `asset()` w szablonach.
4. ✅ **System kategorii** – encja Category, relacja z Dream, panel administratora do zarządzania kategoriami, dropdown w formularzu marzeń.
5. ✅ **Aktualizacja DreamRepository** – metody getDistinctCategories i getDreamsWithFiltersQueryBuilder obsługują teraz relację z Category.
6. ✅ **Aktualizacja szablonów** – dream/index.html.twig, admin/dashboard.html.twig, AdminController.
7. ✅ **Utworzenie CategoryRepository i CategoryType** – brakujące pliki dodane.
8. ✅ **Sekcja zrealizowanych darowizn** – publiczna lista spełnionych marzeń (`/realized`) z podziękowaniami i darczyńcami.
9. ✅ **Lista darczyńców w szczegółach marzenia** – wyświetlanie informacji o darczyńcach, zdjęcia i wiadomości.
10. ✅ **Panel dyrektora – edycja podziękowań** – możliwość dodania/edycji zdjęcia i wiadomości dla każdej darowizny.
11. ✅ **Rejestracja i zarządzanie domami dziecka przez dyrektora** – formularz rejestracji, edycji, weryfikacja przez admina, blokada dodawania dzieci/marzeń przed weryfikacją.
12. ✅ **Rola Super Admin** – możliwość przypisania użytkownikowi ról ROLE_ADMIN i ROLE_DIRECTOR jednocześnie, pozwalająca na dostęp do panelu admina i dyrektora bez przelogowywania.
13. ✅ **Rozróżnienie rejestracji użytkownika (ROLE_USER) i dyrektora (ROLE_DIRECTOR)** – dodanie pola wyboru typu konta w formularzu rejestracji, automatyczne przypisanie odpowiedniej roli.
14. ✅ **Dodanie typu Enum dla statusów**:
   - Utworzono `App\Enum\DreamStatus` i `App\Enum\DreamFulfillmentStatus`.
   - Zaktualizowano encje `Dream` i `DreamFulfillment` do używania enumów zamiast stringów.
   - Zaktualizowano `DevController` do używania wartości enum.
   - Zachowano kompatybilność wsteczną (settery akceptują zarówno enum, jak i string).
15. ✅ **Walidacja formularzy** (Constraints):
   - Dodano szczegółowe walidacje do `DreamType`:
     - `productTitle` (NotBlank, Length 3‑255),
     - `productUrl` (NotBlank, Url),
     - `originalProductUrl` (Url, opcjonalne),
     - `affiliateTrackingId` (Length max 255, opcjonalne),
     - `affiliateUrl` (Url, opcjonalne),
     - `productPrice` (NotBlank, PositiveOrZero),
     - `description` (NotBlank, Length max 100),
     - `quantityNeeded` (NotBlank, Positive, Range 1‑1000),
     - `isUrgent` (NotBlank),
     - `child` (NotBlank).
   - Dodano walidacje do `DreamFulfillmentType`:
     - `donorName` (Length 2‑255, opcjonalne),
     - `donorEmail` (Email, Length max 255, opcjonalne),
     - `donorNickname` (Length max 100, opcjonalne),
     - `quantityFulfilled` (NotBlank, Positive, Range 1‑100),
     - `amount` (PositiveOrZero, opcjonalne).
   - Wszystkie walidacje są automatycznie wyświetlane w formularzach dzięki komponentowi Symfony Form.
16. 🔄 **Dodanie event subscriberów** do automatycznej aktualizacji pól `updatedAt`.
17. ✅ **Zapis logów ważnych operacji** – dodano MonologBundle, skonfigurowano logowanie do plików (w tym osobne logi dla Doctrine i Messenger). Działa również logowanie w konsoli.
18. ✅ **Naprawa systemu kolejek (Messenger)** – dodano brakujący pakiet `symfony/doctrine-messenger`, skonfigurowano transport `async` z Doctrine, dodano transport `failed` oraz włączono logging w konfiguracji messenger.
19. ✅ **Utworzenie pliku .env.local** – przykładowa konfiguracja dla środowiska deweloperskiego i produkcyjnego, zawierająca ustawienia bazy danych, mailera, messengera oraz zwiększony limit pamięci.
20. ✅ **Aktualizacja .gitignore** – dodano wykluczenie plików .env.local.
21. ✅ **Konfiguracja bezpieczeństwa (trusted_hosts, trusted_proxies)** – dodano zmienne środowiskowe TRUSTED_HOSTS i TRUSTED_PROXIES oraz odpowiednie ustawienia w framework.yaml.
22. ✅ **Pełna konfiguracja Monolog** – utworzono plik config/packages/monolog.yaml z kanałami dla deprecation, doctrine, messenger oraz handlerami zapisującymi do oddzielnych plików.
23. ✅ **Aktualizacja framework.yaml** – dodano konfigurację sesji, routera UTF-8, logowania na poziomie framework oraz trusted hosts/proxies.

### Faza 7 – Rozróżnienie rejestracji użytkownika i dyrektora
1. **Modyfikacja RegistrationFormType**:
   - Dodanie pola `accountType` (ChoiceType) z opcjami `user` (zwykły użytkownik) i `director` (dyrektor domu dziecka).
   - Domyślnie wybrana opcja `user`.
2. **Aktualizacja RegistrationController::register**:
   - Odczytywanie wartości `accountType` z formularza.
   - Przypisanie odpowiedniej roli (`ROLE_USER` lub `ROLE_DIRECTOR`).
   - Jeśli wybrano `director`, automatyczne utworzenie pustego rekordu `Orphanage` (niezweryfikowanego) i powiązanie z użytkownikiem (opcjonalnie).
3. **Dostosowanie szablonu rejestracji**:
   - Wyświetlenie pola wyboru typu konta.
   - Dodanie krótkiego opisu dla każdej opcji.
4. **Aktualizacja logiki weryfikacji**:
   - Dla dyrektora: wymagana późniejsza rejestracja domu dziecka (lub automatyczne utworzenie pustego) i weryfikacja przez admina.
   - Dla zwykłego użytkownika: brak dodatkowych kroków.
5. **Testy**:
   - Przetestowanie rejestracji obu typów kont.
   - Sprawdzenie, czy role są poprawnie przypisane.

### Faza 8 – Aktualizacja produkcji i wdrożenie

#### Dane dostępu do serwera produkcyjnego
- **Host**: h69.seohost.pl
- **Port SSH**: 57185
- **Użytkownik**: srv101355
- **Hasło**: Oat8xtiPmiUj
- **Ścieżka do aplikacji**: `/home/srv101355/domains/helpdreams.pl/public_panel/` (domyślna lokalizacja dla hostingu SEOhost)

#### Procedura aktualizacji środowiska produkcyjnego
1. **Zapisanie zmian w repozytorium Git** (na lokalnej maszynie):
   ```bash
   git add .
   git commit -m "Opis zmian"
   git push origin main
   ```

2. **Logowanie na serwer produkcyjny**:
   ```bash
   sshpass -p 'Oat8xtiPmiUj' ssh srv101355@h69.seohost.pl -p 57185
   ```

3. **Przejście do katalogu aplikacji** (po zalogowaniu):
   ```bash
   cd /home/srv101355/domains/helpdreams.pl/public_panel/
   ```

4. **Pobranie najnowszego kodu**:
   ```bash
   git pull origin main
   ```

5. **Instalacja zależności Composer** (w trybie produkcyjnym):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

6. **Uruchomienie migracji bazodanowych**:
   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction --env=prod
   ```

7. **Czyszczenie cache Symfony**:
   ```bash
   php bin/console cache:clear --env=prod --no-debug
   ```

8. **Uruchomienie kompilacji assetów** (jeśli używane):
   ```bash
   npm run build
   ```

9. **Restart usługi PHP-FPM** (jeśli potrzebny):
   ```bash
   sudo systemctl reload php-fpm
   ```

#### Uruchomienie systemu kolejek (Symfony Messenger) na serwerze produkcyjnym
System kolejek wymaga uruchomienia konsumenta (worker), który będzie stale nasłuchiwał wiadomości w tle. Na hostingu współdzielonym (SEOhost) zaleca się użycie **cron job** do okresowego uruchamiania konsumenta w trybie "messages:consume" z limitem czasu.

1. **Konfiguracja konsumenta w środowisku produkcyjnym**:
   - Upewnij się, że w `.env.prod` (lub odpowiednich zmiennych środowiskowych) ustawiony jest transport asynchroniczny, np.:
     ```
     MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
     ```

2. **Uruchomienie konsumenta ręcznie** (do testów):
   ```bash
   php bin/console messenger:consume async --time-limit=3600 --env=prod
   ```

3. **Konfiguracja cron job na serwerze SEOhost**:
   - Zaloguj się do panelu klienta SEOhost (https://h69.seohost.pl:2083) i przejdź do sekcji **Cron Jobs**.
   - Dodaj nowe zadanie cron z następującymi parametrami:
     - **Minuta**: `*/5` (co 5 minut)
     - **Godzina**: `*`
     - **Dzień miesiąca**: `*`
     - **Miesiąc**: `*`
     - **Dzień tygodnia**: `*`
     - **Komenda**:
       ```bash
       cd /home/srv101355/domains/helpdreams.pl/public_panel && /usr/local/bin/php bin/console messenger:consume async --time-limit=300 --env=prod >> var/log/messenger.log 2>&1
       ```
     - Opis: Zadanie będzie uruchamiane co 5 minut i przetwarzało wiadomości przez maksymalnie 300 sekund (5 minut), a logi zostaną zapisane w `var/log/messenger.log`.

4. **Monitorowanie logów konsumenta**:
   ```bash
   tail -f var/log/messenger.log
   ```

#### Kopia zapasowa bazy danych przed migracjami
Przed uruchomieniem migracji zaleca się wykonanie kopii zapasowej bazy danych:
```bash
mysqldump -u srv101355_help_backend -p srv101355_help_backend > backup_$(date +%Y%m%d_%H%M%S).sql
```
Hasło do bazy danych znajduje się w zmiennej `DATABASE_URL` w pliku `.env.prod.local`.

#### Monitorowanie błędów po wdrożeniu
- **Logi Symfony**: `tail -f var/log/prod.log`
- **Logi serwera web**: `tail -f /home/srv101355/logs/helpdreams.pl.error.log`
- **Logi konsumenta Messenger**: `tail -f var/log/messenger.log`

#### Testy funkcjonalne po wdrożeniu
Po wdrożeniu przetestuj kluczowe funkcjonalności:
1. Strona główna (`https://helpdreams.pl/`)
2. Lista marzeń (`/dreams`)
3. Logowanie (`/login`)
4. Panel administratora (`/admin`)
5. Panel dyrektora (`/director`)
6. Formularz darowizny (dla anonimowego użytkownika)
7. System powiadomień (sprawdź, czy emaile są wysyłane)

### Faza 9 – Testy
1. **Stworzenie testów jednostkowych** dla encji i repozytoriów.
2. **Testy funkcjonalne** dla kontrolerów.

### Faza 10 – Testy end‑to‑end (flow aplikacji)
1. **Scenariusz 1: Rejestracja dyrektora i weryfikacja przez admina**
   - Użytkownik wchodzi na stronę główną i klika „Rejestracja”.
   - Wybiera opcję „Dyrektor domu dziecka” w formularzu rejestracji.
   - Wypełnia dane osobowe (email, nazwa użytkownika, hasło) oraz dane domu dziecka (nazwa, adres, miasto, region, kod pocztowy, email kontaktowy, telefon).
   - Po rejestracji otrzymuje rolę `ROLE_DIRECTOR` i jest przekierowany do panelu dyrektora.
   - W panelu dyrektora widzi komunikat, że dom dziecka oczekuje na weryfikację.
   - Administrator loguje się do panelu administracyjnego, przechodzi do zakładki „Domy dziecka”.
   - Administrator znajduje nowo zarejestrowany dom dziecka i klika „Zweryfikuj”.
   - Dyrektor po odświeżeniu panelu widzi, że dom dziecka jest już zweryfikowany.

2. **Scenariusz 2: Dyrektor dodaje dziecko i marzenie**
   - Zalogowany dyrektor (z zweryfikowanym domem dziecka) przechodzi do zakładki „Dzieci”.
   - Klika „Dodaj dziecko”, wypełnia formularz (imię, wiek, opis) i zapisuje.
   - Nowe dziecko pojawia się na liście dzieci.
   - Dyrektor przechodzi do zakładki „Nasze marzenia”.
   - Klika „Dodaj marzenie”, wypełnia formularz (tytuł produktu, link, cena, kategoria, opis, potrzebna ilość, pilne, wybiera dziecko z listy).
   - Po zapisaniu marzenie pojawia się na liście marzeń dyrektora ze statusem „Oczekujące”.

3. **Scenariusz 3: Użytkownik anonimowy przegląda marzenia i składa darowiznę**
   - Użytkownik niezalogowany odwiedza stronę główną i klika „Marzenia”.
   - Przegląda listę marzeń, może używać filtrów (kategoria, region, pilne).
   - Wybiera marzenie z listy i przechodzi do szczegółów.
   - Na stronie szczegółów klika „Chcę pomóc!”.
   - Wypełnia formularz darowizny (imię, email, pseudonim, ilość, opcjonalnie anonimowość) – bez konieczności logowania.
   - Po złożeniu darowizny widzi komunikat sukcesu, a ilość zebrana w marzeniu zwiększa się.

4. **Scenariusz 4: Administrator zarządza marzeniami i darowiznami**
   - Administrator loguje się do panelu administracyjnego.
   - W zakładce „Marzenia” zmienia status marzenia z „Oczekujące” na „Zweryfikowane”.
   - W zakładce „Darowizny” przegląda listę wszystkich darowizn.
   - W zakładce „Użytkownicy” zmienia rolę użytkownika na „Super Admin” (Admin + Dyrektor).

5. **Scenariusz 5: Dyrektor edytuje podziękowania za darowiznę**
   - Dyrektor loguje się do panelu dyrektora.
   - Przechodzi do szczegółów marzenia, które ma już darowizny.
   - Dla każdej darowizny (jeśli dotyczy jego domu dziecka) może edytować podziękowanie (dodawać zdjęcie dziecka z prezentem i wiadomość).
   - Po zapisaniu zmiany są widoczne w publicznej sekcji „Zrealizowane marzenia”.

6. **Scenariusz 6: Super Admin działa w obu panelach**
   - Użytkownik z rolą Super Admin (ROLE_ADMIN + ROLE_DIRECTOR) loguje się.
   - Widzi w navbarze linki do panelu administracyjnego i panelu dyrektora.
   - Może przeglądać panel administracyjny (wszystkie funkcje admina).
   - Może przeglądać panel dyrektora (lista dzieci, marzeń), ale nie może dodawać dzieci/marzeń, ponieważ nie ma przypisanego domu dziecka (lub ma, jeśli został mu przypisany).

7. **Weryfikacja danych po każdym scenariuszu**
   - Sprawdzenie, czy dane zapisują się poprawnie w bazie danych.
   - Sprawdzenie, czy komunikaty błędów są wyświetlane odpowiednio (np. próba dodania dziecka bez weryfikacji domu dziecka).
   - Sprawdzenie, czy uprawnienia działają (brak dostępu do nieautoryzowanych ścieżek).

### Faza 15 – System kolejek i powiadomień
1. **Cel systemu**:
   - Automatyczne wysyłanie powiadomień email do odpowiednich użytkowników w reakcji na zdarzenia w systemie.
   - Przechowywanie powiadomień w panelu użytkownika (np. „Masz nową darowiznę”, „Twój dom dziecka został zweryfikowany”).
   - Asynchroniczne przetwarzanie wiadomości za pomocą kolejek (Symfony Messenger) w celu uniknięcia blokowania interfejsu.

2. **Wymagane powiadomienia**:
   - **Dla dyrektora**:
     - Nowa darowizna dla marzenia z jego domu dziecka (email + powiadomienie w panelu).
     - Zmiana statusu marzenia (np. weryfikacja przez admina, oznaczenie jako „w realizacji”).
     - Prośba o dodanie podziękowania po zrealizowaniu darowizny.
   - **Dla administratora**:
     - Nowy dom dziecka oczekujący na weryfikację.
     - Nowe marzenie oczekujące na weryfikację.
     - Nowy użytkownik z rolą dyrektora.
   - **Dla darczyńcy (jeśli podał email)**:
     - Potwierdzenie złożenia darowizny.
     - Informacja o zmianie statusu darowizny (np. „prezent dostarczony”).
     - Podziękowanie od dziecka (gdy dyrektor doda zdjęcie/wiadomość).
   - **Dla użytkownika (rejestracja)**:
     - Powitalny email po rejestracji.
     - Prośba o weryfikację konta (jeśli dodamy tę funkcjonalność).

3. **Implementacja**:
   - **Symfony Messenger** – konfiguracja transportu (sync dla dev, async dla prod z użyciem Doctrine lub RabbitMQ).
   - **Encja Notification** – przechowująca powiadomienia w bazie danych (odbiorca, typ, tytuł, treść, odczytane, data).
   - **Event Subscribers** – nasłuchiwanie zdarzeń (np. `DreamFulfillmentCreatedEvent`, `OrphanageVerifiedEvent`) i wysyłanie wiadomości do kolejki.
   - **Handler wiadomości** – przetwarzanie wiadomości z kolejki: wysyłanie emaili (via Symfony Mailer) oraz zapisywanie powiadomień w bazie.
   - **Panel powiadomień** – endpoint API lub strona w panelu użytkownika/dyrektora/admina z listą nieprzeczytanych powiadomień oraz możliwością oznaczenia jako przeczytane.

4. **Kroki implementacyjne**:
   - Instalacja i konfiguracja Symfony Messenger, Symfony Mailer.
   - Stworzenie encji `Notification` i migracji.
   - Utworzenie klas zdarzeń (events) dla kluczowych akcji.
   - Utworzenie subscriberów, które będą publikować wiadomości do kolejki.
   - Utworzenie handlerów, które będą wysyłać emaile i zapisywać powiadomienia.
   - Dodanie widoku powiadomień w panelach użytkowników.
   - Testy asynchronicznego wysyłania w środowisku deweloperskim i produkcyjnym.

5. **Bezpieczeństwo i wydajność**:
   - Upewnienie się, że dane użytkowników (emaile) nie są wyciekane do nieuprawnionych osób.
   - Ograniczenie częstotliwości wysyłanych powiadomień, aby nie spamować użytkowników.
   - Monitorowanie kolejki pod kątem zaległych wiadomości.

### Faza 12 – Optymalizacja i skalowanie
1. **Konfiguracja środowiska produkcyjnego** (cache, środowisko `prod`).
2. **Monitoring** (logi, błędy).
3. **Ewentualna integracja z usługami reklamowymi** (Google AdSense).

### Faza 13 – Sieć afiliacyjna i śledzenie konwersji – ✅ UKOŃCZONA
**Cel**: Umożliwienie finansowania marzeń poprzez linki partnerskie (afiliacyjne) zamiast bezpośrednich darowizn. Platforma nie zbiera pieniędzy, lecz przekierowuje użytkowników do sklepów partnerskich i śledzi kliknięcia oraz zakupy.

#### ✅ **13.1. Rozszerzenie encji Dream**
- Dodano pola afiliacyjne: `affiliatePartner` (string), `affiliateTrackingId` (string), `originalProductUrl` (text), `affiliateUrl` (text), `purchasedQuantity` (int).
- Dodano relacje `OneToMany` do nowych encji `AffiliateClick` i `AffiliateConversion`.
- Zaktualizowano konstruktor oraz dodano gettery i settery.

#### ✅ **13.2. Nowe encje do śledzenia**
- **AffiliateClick** – przechowuje każde kliknięcie w link afiliacyjny (IP, user‑agent, sesja, timestamp).
- **AffiliateConversion** – rejestruje zakupy (numer zamówienia, kwota, prowizja, ilość sztuk, powiązanie z kliknięciem).
- Dla obu encji utworzono repozytoria z metodami pomocniczymi (`countClicksForDream`, `getTotalPurchasedQuantityForDream`).

#### ✅ **13.3. Migracja bazy danych**
- Utworzono migrację `Version20251218000000`, która dodaje tabele `affiliate_clicks`, `affiliate_conversions` oraz nowe kolumny w tabeli `dreams`.

#### ✅ **13.4. Formularz marzenia z polami afiliacyjnymi**
- Zaktualizowano `DreamType` o pola: `originalProductUrl`, `affiliatePartner` (dropdown: Ceneo, Amazon, Allegro, Inny), `affiliateTrackingId`, `affiliateUrl`.
- Pola są opcjonalne i wyświetlane w formularzu dodawania/edycji marzenia.

#### ✅ **13.5. Automatyczne generowanie linków afiliacyjnych**
- Utworzono serwis `AffiliateLinkGenerator`, który na podstawie partnera i oryginalnego URL tworzy link z odpowiednimi parametrami śledzącymi (np. `?aff_id=...` dla Allegro).
- Dodano event subscriber `DreamAffiliateSubscriber`, który automatycznie wywołuje generator przy zapisie marzenia (`prePersist`/`preUpdate`) i ustawia `affiliateUrl`.

#### ✅ **13.6. Śledzenie kliknięć i przekierowanie**
- Utworzono `AffiliateController` z akcją `/go/{id}`.
- Endpoint rejestruje kliknięcie (`AffiliateClick`) z danymi klienta, a następnie przekierowuje użytkownika na `affiliateUrl` (lub `productUrl`, jeśli afiliacyjny brak).

#### ✅ **13.7. Panel administratora dla sieci afiliacyjnych**
- Utworzono `AdminAffiliateController` z dashboardem (`/admin/affiliate`) prezentującym statystyki dla wszystkich marzeń (kliknięcia, zakupione sztuki, współczynnik konwersji).
- Możliwość ręcznego dodawania konwersji (formularz) oraz usuwania istniejących.
- Kontroler automatycznie aktualizuje `Dream::purchasedQuantity` po każdej zmianie.
- Dodano link do panelu afiliacyjnego w dashboardzie administratora (`admin/dashboard.html.twig`).

#### ✅ **13.8. Front – wyświetlanie linków afiliacyjnych**
- Zaktualizowano szablon `dream/show.html.twig`:
  - Sekcja **"Kup przez afiliację"** z przyciskiem "Kup teraz (link afiliacyjny)" prowadzącym do `/go/{id}`.
  - Statystyki afiliacyjne (liczba kliknięć, zakupione sztuki, partner).
  - Informacja o partnerze i linku afiliacyjnym.
- Zaktualizowano szablon `dream/index.html.twig`:
  - Ikona 🔗 i znacznik "Afiliacja" dla marzeń z partnerem afiliacyjnym.
  - Nowe kolumny statystyk: `Zakupione (afiliacja)`, `Pozostało` (uwzględniające oba typy finansowania).
  - Dodano filtr **"Typ finansowania"** (afiliacyjne/darowizny) w sekcji filtrów.

#### ✅ **13.9. Backend – obsługa filtrowania po typie finansowania**
- Zaktualizowano `DreamRepository::getDreamsWithFiltersQueryBuilder` o obsługę parametru `fundingType` (`affiliate` / `donation`).
- Zaktualizowano `DreamController::index` o przekazywanie parametru `fundingType` do widoku.

#### ✅ **13.10. Integracja z istniejącym modelem darowizn**
- Dodano pole `type` (enum: `donation` / `affiliate`) do encji `DreamFulfillment`.
- Zaktualizowano `DreamRepository` o metodę `updatePurchasedQuantity`, która przelicza sumę `quantity` z `AffiliateConversion` i aktualizuje `Dream::purchasedQuantity`.
- Logika spełniania marzeń uwzględnia teraz **dwie składowe**: `quantityFulfilled` (darowizny) + `purchasedQuantity` (zakupy afiliacyjne).

#### ✅ **13.11. Dane testowe**
- Rozszerzono `DevController::fillData()` o przykładowe dane afiliacyjne:
  - Dla marzenia "Rower górski" ustawiono partnera Allegro z linkiem afiliacyjnym.
  - Dodano kliknięcia (`AffiliateClick`) i konwersje (`AffiliateConversion`).
  - Automatycznie zaktualizowano `purchasedQuantity`.

#### ✅ **13.12. Dokumentacja i flow działania**
- Dodano szczegółowy opis flow (krok po kroku) w sekcji **6. Flow sieci afiliacyjnej – instrukcja krok po kroku** (poniżej).

---

## 6. Flow sieci afiliacyjnej – instrukcja krok po kroku

### 6.1. Dla dyrektora (tworzenie marzenia z linkiem afiliacyjnym)
1. **Zaloguj się** jako dyrektor (ROLE_DIRECTOR) i przejdź do panelu dyrektora.
2. **Kliknij "Dodaj nowe marzenie"** – otworzy się formularz z nowymi polami afiliacyjnymi.
3. **Wypełnij pola afiliacyjne**:
   - **Oryginalny link produktu (afiliacyjny)** – bezpośredni URL produktu w sklepie partnerskim.
   - **Partner afiliacyjny** – wybierz z listy (Ceneo, Amazon, Allegro, Inny).
   - **ID śledzenia afiliacyjnego** – wpisz swój kod z programu partnerskiego (opcjonalnie).
   - **Wygenerowany link afiliacyjny** – pozostaw puste, system wypełni je automatycznie.
4. **Uzupełnij standardowe dane** (tytuł, cena, dziecko, ilość potrzebna itp.) i zapisz marzenie.
5. **System automatycznie generuje link afiliacyjny** na podstawie partnera i ID śledzenia, dodając odpowiednie parametry (np. `?aff_id=...` dla Allegro).
6. **Marzenie trafia do bazy** i po weryfikacji przez administratora jest publikowane.

### 6.2. Dla użytkownika (zakup przez afiliację)
1. **Przeglądaj listę marzeń** (`/dreams`) – marzenia z linkiem afiliacyjnym mogą być oznaczone ikoną 🔗.
2. **Wejdź w szczegóły marzenia** (`/dreams/{id}`) – zobaczysz sekcję "Kup przez afiliację" z przyciskiem "Kup teraz (link afiliacyjny)".
3. **Kliknij przycisk** – zostaniesz przekierowany na `/go/{id}`, gdzie system zarejestruje kliknięcie (`AffiliateClick`) i natychmiast przekieruje Cię do sklepu partnerskiego.
4. **Dokonaj zakupu** w sklepie – korzystając z linku zawierającego nasz kod śledzący.
5. **Administrator lub dyrektor ręcznie dodaje konwersję** w panelu administratora (`/admin/affiliate`), podając dane zamówienia (numer, kwotę, prowizję, ilość sztuk).
6. **System aktualizuje liczbę zakupionych sztuk** (`Dream::purchasedQuantity`) i sprawdza, czy marzenie zostało już spełnione.

### 6.3. Dla administratora (pełna kontrola)
- **Dashboard afiliacyjny** (`/admin/affiliate`) – tabela ze statystykami wszystkich marzeń (kliknięcia, zakupy, współczynnik konwersji).
- **Ręczne dodawanie/edycja/usuwanie konwersji** – formularz dostępny z poziomu dashboardu.
- **Monitorowanie efektywności** – na podstawie współczynnika konwersji można optymalizować wybór partnerów lub treść opisu marzenia.

### 6.4. Jak przygotować linki afiliacyjne (praktyczne wskazówki)
1. **Zarejestruj się w programach partnerskich** wybranych sklepów (Allegro Partners, CeneoLab, Amazon Associates) i odbierz swój identyfikator śledzenia.
2. **Skopiuj bezpośredni URL produktu** ze sklepu.
3. **W formularzu marzenia** wklej URL w polu "Oryginalny link produktu", wybierz partnera i opcjonalnie wpisz identyfikator śledzenia.
4. **System samodzielnie doda parametry śledzące** zgodne z wymaganiami danego partnera.
5. **Po zapisie sprawdź wygenerowany link** w szczegółach marzenia – powinien prowadzić do produktu z Twoim kodem śledzącym.

### 6.5. Przepływ danych w bazie
- **Dream** – przechowuje dane afiliacyjne (`affiliatePartner`, `affiliateTrackingId`, `originalProductUrl`, `affiliateUrl`, `purchasedQuantity`).
- **AffiliateClick** – rejestruje każde kliknięcie w link afiliacyjny (IP, user‑agent, sesja, timestamp).
- **AffiliateConversion** – rejestruje każdy zakup (numer zamówienia, kwota, prowizja, ilość sztuk).
- **DreamFulfillment** – rozszerzone o pole `type` (`donation` / `affiliate`), na razie używane tylko dla darowizn bezpośrednich.

---

## 7. Uwagi bezpieczeństwa (audyt)

### 4.1. Konfiguracja security.yaml
- Upewnić się, że ścieżki `/admin`, `/director` są chronione odpowiednimi rolami.
- Sprawdzić, czy nie ma otwartych ścieżek do modyfikacji danych bez autoryzacji.

### 4.2. CSRF
- Formularze Symfony (tworzone za pomocą `createForm`) domyślnie zawierają token CSRF.
- Należy upewnić się, że wszystkie niestandardowe formularze POST również go używają.

### 4.3. XSS
- Twig automatycznie escape’uje zmienne wyświetlane za pomocą `{{ ... }}`.
- Jeśli gdzieś używamy `|raw`, należy upewnić się, że dane są wcześniej oczyszczone.

### 4.4. SQL Injection
- Wszystkie zapytania Doctrine DQL i QueryBuilder używają parametryzacji.
- Należy unikać bezpośredniej konkatenacji w zapytaniach DQL.

### 4.5. Upload plików
- Obecnie nie ma funkcji uploadu; jeśli zostanie dodana, należy:
  - Walidować typy MIME i rozmiary.
  - Przechowywać pliki poza katalogiem publicznym lub użyć bezpiecznej konfiguracji.

### 4.6. Rejestracja użytkownika vs dyrektora
- Formularz rejestracji zawiera pole wyboru `accountType` z opcjami `user` (zwykły użytkownik) i `director` (dyrektor domu dziecka).
- W zależności od wyboru użytkownik otrzymuje odpowiednią rolę: `ROLE_USER` lub `ROLE_DIRECTOR`.
- Dyrektorzy muszą następnie zarejestrować swój dom dziecka (lub zostaje dla nich automatycznie utworzony niezweryfikowany rekord) i oczekiwać na weryfikację przez administratora.
- Zwykli użytkownicy nie mają dostępu do panelu dyrektora i nie mogą dodawać dzieci/marzeń.

### 4.7. Rola Super Admin (Admin + Dyrektor)
- W panelu administratora istnieje opcja "Super Admin", która przypisuje użytkownikowi trzy role: `ROLE_ADMIN`, `ROLE_DIRECTOR` oraz `ROLE_USER`.
- Użytkownik z tymi rolami ma jednoczesny dostęp do panelu administratora oraz panelu dyrektora bez konieczności przelogowywania.
- W panelu dyrektora Super Admin może przeglądać listy dzieci i marzeń, ale nie może dodawać/edycji bez przypisanego domu dziecka (brak encji `Orphanage` powiązanej z użytkownikiem).
- Logika kontrolerów dyrektora została zaktualizowana, aby uwzględniać ten przypadek i wyświetlać odpowiednie komunikaty.

### 4.8. System powiadomień
- Powiadomienia email są wysyłane asynchronicznie za pomocą Symfony Messenger, co zapobiega blokowaniu interfejsu użytkownika.
- Adresy email odbiorców są weryfikowane przed wysłaniem (istnienie użytkownika, zgoda na komunikację).
- W panelu użytkownika przechowywana jest historia powiadomień, do której dostęp ma tylko zalogowany użytkownik.
- Należy zapewnić możliwość rezygnacji z powiadomień email (opcja w ustawieniach konta).

### 4.9. Hasła
- Używany jest `UserPasswordHasherInterface` z algorytmem bcrypt (domyślnie w Symfony).
- Należy wymusić minimalną siłę hasła podczas rejestracji.

---

## 5. Dane testowe

Do szybkiego przetestowania aplikacji w środowisku deweloperskim (`APP_ENV=dev`) dostępna jest trasa `/dev/fill-data`, która wypełnia bazę przykładowymi rekordami:

- **Użytkownicy** (hasło dla wszystkich: `password123`):
  - Administrator: `admin@example.com` (ROLE_ADMIN)
  - Dyrektor domu dziecka: `director@example.com` (ROLE_DIRECTOR)
  - Zwykły użytkownik: `user@example.com` (ROLE_USER)

- **Dom dziecka**:
  - „Dom Dziecka w Warszawie” (zweryfikowany, z przypisanym dyrektorem)

- **Dzieci**:
  - Jan (10 lat)
  - Anna (14 lat)

- **Marzenia**:
  1. Rower górski (status: `approved`, cena 599,99 zł, kategoria Sport)
  2. Zestaw malarski (status: `pending`, pilne, cena 129,50 zł)
  3. Komiksy (status: `approved`, potrzebna ilość: 5, zebrano: 2)

- **Darowizny**:
  - Dwie darowizny dla komiksów (jedna zakończona, druga w trakcie)

**Uwaga**: Ze względu na ograniczenia walidacji encji `Dream` i `DreamFulfillment`, statusy `approved`, `completed` oraz `pending` zostały ustawione z pominięciem setterów (przez refleksję). W produkcyjnym kodzie należy dostosować metody `setStatus()` w tych encjach tak, aby akceptowały odpowiednie wartości.

Trasa `/dev/fill-data` działa wyłącznie w środowisku deweloperskim i nie wymaga autoryzacji – jej wywołanie natychmiast wstawia powyższe dane do bazy. Po uruchomieniu możesz zalogować się na dowolne z podanych kont i przeglądać listę marzeń oraz ich szczegóły.

---

## 6. Notatki

- **Data rozpoczęcia planu**: 2025-12-16
- **Ostatnia aktualizacja**: 2025-12-18 (dodanie pełnej implementacji sieci afiliacyjnej – Faza 13)
- **Wersja aplikacji**: w rozwoju
- **Ostatnia migracja bazy danych**: Version20251218000000 (afiliacyjna)

---
*Dokument będzie aktualizowany przy każdym wczytaniu projektu oraz po wprowadzeniu znaczących modyfikacji.*
