# Clean Architecture Anti-Patterns

This document outlines common anti-patterns to avoid when working with the Yii3 clean architecture in this repository. Keeping your code clean ensures consistency, security, and makes it easy to refactor.

---

## Anti-Pattern 1: Bypassing the Service Layer

### ❌ Bad Code (Calling Repository from Controller)
```php
// admin-api/src/Controller/User/Controller.php
public function view(#[RouteArgument] string $id): ResponseInterface
{
    // Violation: Controller bypasses Service and directly queries Repository
    $user = $this->userRepo->getOneById(new Uuid($id));
    
    if ($user === null) {
        throw new NotFoundException();
    }
    
    return $this->responseFactory->ok(UserResponse::fromModel($user));
}
```

###  Good Code
```php
// admin-api/src/Controller/User/Controller.php
public function view(#[RouteArgument] string $id): ResponseInterface
{
    // OK: Controller delegates to Service
    $user = $this->userService->getNotBannedUserById(new Uuid($id));
    
    if ($user === null) {
        throw new NotFoundException();
    }
    
    return $this->responseFactory->ok(UserResponse::fromModel($user));
}
```
> [!IMPORTANT]
> **Why?** The Service layer holds the transaction boundaries, access controls, logging, and business rules. Querying repositories directly in controllers leads to logic leaks and code duplication.

---

## Anti-Pattern 2: Leaking Raw Models to the API Response

### ❌ Bad Code (Leaking Active Record Model)
```php
// admin-api/src/Controller/User/Controller.php
public function create(CreateRequest $input): ResponseInterface
{
    $user = $this->userService->create(new CreateDto(...));
    
    // Violation: Returning raw Active Record model
    return $this->responseFactory->created($user); 
}
```

###  Good Code
```php
// admin-api/src/Controller/User/Controller.php
public function create(CreateRequest $input): ResponseInterface
{
    $user = $this->userService->create(new CreateDto(...));
    
    // OK: Map to Response DTO first
    return $this->responseFactory->created(
        UserResponse::fromModel($user)
    );
}
```
> [!WARNING]
> **Why?** Exposing Active Record models directly couples your public API contract to database schemas. If a column name changes, the public API breaks. It also risks exposing sensitive columns like password hashes.

---

## Anti-Pattern 3: Saving Active Record Models in Services or Controllers

### ❌ Bad Code (Calling `$model->save()` in Service)
```php
// common/src/App/Service/User/Service.php
public function updateEmail(User $user, Email $email): User
{
    $user->setEmail($email);
    
    // Violation: Direct model saving bypasses Repository lifecycle
    $user->save(); 
    
    return $user;
}
```

###  Good Code
```php
// common/src/App/Service/User/Service.php
public function updateEmail(User $user, Email $email): User
{
    $user->setEmail($email);
    
    // OK: Handled by Repository
    return $this->userRepo->save($user);
}
```
> [!NOTE]
> **Why?** Repositories own the persistence lifecycle. The Repository's `save()` method centrally handles ID generation (UUIDs for new records), auditing/timestamps (`created_at` and `updated_at`), database sync, and caching.

---

## Anti-Pattern 4: Writing Business Logic inside Active Record Models

### ❌ Bad Code (Business/Validation logic inside model)
```php
// common/src/App/Models/User.php
class User extends AbstractModel 
{
    // Violation: Business check inside table mapping model
    public function canAccessDashboard(): bool 
    {
        return $this->role === AdminUserRole::SUPER_ADMIN && !$this->is_banned;
    }
}
```

###  Good Code
```php
// common/src/App/Service/Auth/Service.php or similar Auth Service
public function canAccessDashboard(User $user): bool 
{
    // OK: Kept in the service layer
    return $user->getRole() === AdminUserRole::SUPER_ADMIN && !$user->getBannedAt();
}
```
> [!NOTE]
> Active Record models should be "dumb" data structures representing database records. Keep all business validation, permissions, external dependencies, and logic in **Domain Services**.

---

## Anti-Pattern 5: Passing Request Input/Framework Objects directly to Services

### ❌ Bad Code (HTTP Request class passed to Service)
```php
// common/src/App/Service/User/Service.php
// Violation: Service depends on HTTP-layer Request DTO
public function create(CreateRequest $request): User 
{
    $user = $this->userRepo->getEmptyModel();
    $user->setEmail($request->email());
    return $this->userRepo->save($user);
}
```

###  Good Code
```php
// common/src/App/Service/User/Service.php
// OK: Service expects simple domain DTO
public function create(CreateDto $dto): User 
{
    $user = $this->userRepo->getEmptyModel();
    $user->setEmail($dto->email);
    return $this->userRepo->save($user);
}
```
> [!IMPORTANT]
> **Why?** The Service layer must be framework-agnostic. Coupling services to HTTP Input/Request DTOs means you cannot reuse the services in Console commands, message queues, or command-line scripts without simulating HTTP requests.
