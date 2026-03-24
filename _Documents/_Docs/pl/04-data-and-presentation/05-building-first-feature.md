# Building your first feature (Controller + Form + Database)

Ten przewodnik pokazuje, jak stworzyć kompletną funkcjonalność w DBM Framework:

* kontroler (HTTP)
* formularz (walidacja)
* serwis (logika biznesowa)
* repozytorium (baza danych)

Całość oparta o system DI (autowiring + konstruktor).

---

## Struktura

Przykładowy moduł:

```
App\Authentication\
├── Controller/
├── Form/
├── Repository/
├── Service/
```

---

# 1. Kontroler

Kontroler odpowiada za obsługę requestów i delegowanie logiki.

```php
class AuthenticationController extends BaseController
{
    public function __construct(
        private readonly AuthenticationService $service,
        private readonly AuthenticationRepository $repository,
        private readonly TranslationInterface $translation,
        private readonly CsrfTokenManager $csrf,
        private readonly FlashBag $flash,
        private readonly Logger $logger
    ) {}

    /**
     * @routing GET '/login' name: login
     */
    public function login(): ResponseInterface
    {
        return $this->render('authentication/login.phtml', [
            'meta' => $this->service->getMetaLogin(),
        ]);
    }

    /**
     * @routing POST '/login/signin' name: login_signin
     */
    public function loginSignin(): ResponseInterface
    {
        $formData = (array) $this->request->getParsedBody();

        $form = new AuthenticationLoginForm(
            $this->csrf,
            $this->translation
        );

        $errors = $form->validate($formData);

        if ($errors) {
            return $this->render('authentication/login.phtml', [
                'validate' => $errors,
            ]);
        }

        $userId = $this->service->authenticateUser(
            $formData['dbm_login'],
            $formData['dbm_password']
        );

        if (!$userId) {
            $this->flash->set(
                $this->translation->trans('auth.login.invalid'),
                'messageWarning'
            );

            return $this->redirect('login');
        }

        $this->setSession(getenv('APP_SESSION_KEY'), $userId);

        $this->flash->set(
            $this->translation->trans('auth.login.success'),
            'messageSuccess'
        );

        return $this->redirect('account');
    }
}
```

---

# 2. Formularz i walidacja

Formularz odpowiada za walidację danych wejściowych.

```php
class AuthenticationLoginForm extends Validator
{
    public function __construct(
        CsrfTokenManager $csrf,
        protected ?TranslationInterface $translation = null
    ) {
        parent::__construct($translation);
        $this->registerCsrfRule($csrf);
    }

    public function validate(array $data): array
    {
        $this->rules([
            'csrf_token' => ['required', 'csrf'],
            'dbm_login' => ['required', 'string', 'min:2', 'max:60'],
            'dbm_password' => ['required', 'string', 'min:6'],
        ], $data);

        return $this->getErrors();
    }
}
```

---

## Walidacja rozszerzona (custom)

Możesz dodać dodatkową logikę:

```php
if ($this->repository->checkLogin($data['dbm_login'])) {
    $this->registerError(
        'dbm_login',
        $this->translation->trans('auth.login.exists')
    );
}
```

---

# 3. Serwis (logika biznesowa)

Serwis zawiera logikę aplikacji — NIE kontroler.

```php
class AuthenticationService
{
    public function __construct(
        private readonly AuthenticationRepository $repository,
        private readonly TranslationInterface $translation,
        private readonly Logger $logger
    ) {}

    public function authenticateUser(string $login, string $password): ?int
    {
        $result = $this->repository->checkIsUserCorrect([
            'login' => $login,
            'email' => $login,
        ], $password);

        if (!is_numeric($result)) {
            return null;
        }

        return (int) $result;
    }
}
```

---

# 4. Repozytorium (baza danych)

Repozytorium komunikuje się z bazą danych.

```php
class AuthenticationRepository extends AbstractRepository
{
    protected string $table = 'dbm_user';

    public function checkLogin(string $login): bool
    {
        return $this->database->fetch(
            "SELECT 1 FROM dbm_user WHERE login = :login",
            ['login' => $login]
        ) !== null;
    }
}
```

---

# 5. Flow działania

Schemat działania:

```
Request → Controller → Form (Validation)
                    ↓
                Service
                    ↓
               Repository
                    ↓
                Database
```

---

# 6. Flash messages

```php
$this->flash->set('Zalogowano poprawnie', 'messageSuccess');
```

W szablonie:

```php
{% if ($flash = $this->getFlash()): %}
    <div>{{ $flash }}</div>
{% endif %}
```

---

# 7. Tłumaczenia

```php
$this->translation->trans('auth.login.success');
```

Plik:

```
translations/pl/validation.php
```

```php
return [
    'auth.login.success' => 'Zalogowano poprawnie',
];
```

---

# 8. CSRF

```php
<input type="hidden" name="csrf_token" value="{{ $this->csrf() }}">
```

Walidacja:

```php
'csrf_token' => ['required', 'csrf']
```

---

# 9. Najważniejsze zasady

- Kontroler = tylko flow HTTP  
- Serwis = logika biznesowa  
- Formularz = walidacja  
- Repozytorium = baza danych  

---

# 10. Podsumowanie

DBM Framework stosuje podejście:

* **czysty konstruktor (DI)**
* **brak bazy w kontrolerze**
* **separacja odpowiedzialności**

Dzięki temu kod jest:

* czytelny
* testowalny
* łatwy do rozwijania

---

To jest podstawowy wzorzec budowania funkcjonalności w DBM Framework.
