# Development Plan – Portal Pomocy dla Domów Dziecka

Ten dokument opisuje aktualny stan aplikacji, brakujące funkcjonalności, plan implementacji oraz uwagi dotyczące bezpieczeństwa.  
**Aktualizowany przy każdym wczytaniu projektu oraz po wprowadzeniu znaczących modyfikacji.**

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

---

## 3. Plan implementacji (kolejność priorytetowa)

### Faza 1 – Uzupełnienie encji i podstawowych zabezpieczeń
1. **Dodanie relacji DreamFulfillment → User** (nullable, dla zalogowanych darczyńców).
2. **Ustawienie domyślnej roli w konstruktorze User** (`$this->roles = ['ROLE_USER'];`).
3. **Stworzenie migracji** dla nowych pól/relacji.
4. **Aktualizacja konfiguracji ról w security.yaml** (hierarchy: ROLE_USER, ROLE_DIRECTOR, ROLE_ADMIN).

### Faza 2 – Publiczna lista marzeń i szczegóły
1. **Utworzenie DreamController** z akcjami:
   - `public function index(Request $request): Response` (lista z paginacją i filtrami)
   - `public function show(Dream $dream): Response`
2. **Rozszerzenie DreamRepository** o metody wyszukiwania z filtrami.
3. **Szablony** `templates/dream/index.html.twig`, `templates/dream/show.html.twig`.

### Faza 3 – Formularz darowizny
1. **Utworzenie DreamFulfillmentController** z akcją `public function fulfill(Request $request, Dream $dream): Response`.
2. **Stworzenie DreamFulfillmentType** (formularz z danymi darczyńcy i ilością).
3. **Obsługa zapisu** wraz z aktualizacją `Dream::quantityFulfilled`.
4. **Szablon** `templates/dream_fulfillment/fulfill.html.twig`.

### Faza 4 – Panel dyrektora (ROLE_DIRECTOR)
1. **Stworzenie ChildController** (CRUD dla dzieci, dostęp tylko dla director swojego domu dziecka).
2. **Stworzenie DreamController akcje `new`, `edit`, `delete`** (z ograniczeniem do własnego orphanage).
3. **Szablony** dla zarządzania dziećmi i marzeniami.

### Faza 5 – Panel administratora (ROLE_ADMIN)
1. **Utworzenie AdminController** z akcjami:
   - Lista użytkowników z możliwością zmiany ról.
   - Lista domów dziecka do weryfikacji.
   - Lista wszystkich marzeń i darowizn.
2. **Szablony** administracyjne.

### Faza 6 – Usprawnienia i bezpieczeństwo
1. **Dodanie typu Enum dla statusów** (np. klasa DreamStatus, DreamFulfillmentStatus).
2. **Walidacja formularzy** (Constraints).
3. **Dodanie event subscriberów** do automatycznej aktualizacji pól `updatedAt`.
4. **Zapis logów ważnych operacji**.

### Faza 7 – Testy
1. **Stworzenie testów jednostkowych** dla encji i repozytoriów.
2. **Testy funkcjonalne** dla kontrolerów.

---

## 4. Uwagi bezpieczeństwa (audyt)

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

### 4.6. Hasła
- Używany jest `UserPasswordHasherInterface` z algorytmem bcrypt (domyślnie w Symfony).
- Należy wymusić minimalną siłę hasła podczas rejestracji.

---

## 5. Notatki

- **Data rozpoczęcia planu**: 2025-12-16
- **Ostatnia aktualizacja**: 2025-12-16 (utworzenie)
- **Wersja aplikacji**: w rozwoju

---
*Dokument będzie aktualizowany przy każdym wczytaniu projektu oraz po wprowadzeniu znaczących modyfikacji.*
