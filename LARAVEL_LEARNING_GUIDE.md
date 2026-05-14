# Laravel + React (Inertia) Learning Guide

Welcome to your modern web application! This guide breaks down the core concepts we implemented in this project, explaining how the different pieces of Laravel and React work together to create a secure, robust Notes feature.

---

## 1. The Stack: How it all connects
This project uses **Laravel** for the backend (database, security, routing) and **React** for the frontend (UI, state management). They communicate using **Inertia.js**, which acts as a bridge. Instead of building a complex API, Laravel simply returns a React component and passes data directly into it as "props".

---

## 2. Routing (`routes/web.php` and `routes/api.php`)
This is the entry point of your application. When a user visits a URL or an API client makes a request, Laravel looks here to see what to do.

### Web Routes
```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/my-journal', [NoteController::class, 'index'])->name('notes.index');
    // ... other web routes
});
```
These routes are for your browser-based UI. They use the `web` middleware group, which handles things like cookies and sessions. They typically return Inertia views.

### API Routes
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('notes', NoteController::class)->names('api.notes');
});
```
These routes are for external clients, like a mobile app or another service. They are prefixed with `/api/` and are stateless (they use API tokens instead of cookies). They always return raw data, like JSON.

### Named Routes (`->name('...')`)
Giving routes a name is a best practice. It allows you to generate URLs without hardcoding the path, making your application much easier to maintain.
```php
// Generate a URL in a Blade/PHP file
$url = route('notes.index'); // Result: http://localhost/my-journal

// Generate a URL in a React file (using the Ziggy library)
const url = route('notes.update', { note: 5 }); // Result: http://localhost/my-journal/5
```
If you ever change the URL in your route file, the `route()` function will automatically generate the new URL, and you don't have to update your code in dozens of different files.

---

## 3. Database: Models and Migrations

### Migrations (`database/migrations/`)
Migrations are like version control for your database. Instead of manually creating tables in SQL, you write PHP code.
* We created a `notes` table with `title`, `content`, and `notes` columns.
* We added a `user_id` foreign key so every note belongs to a specific user.
* We added `$table->softDeletes()`, which adds a `deleted_at` column.

### Models (`app/Models/Note.php` & `User.php`)
Models are how you interact with the database tables. This concept is called **Eloquent ORM**.
* **Relationships**: In `User.php`, we added `public function notes() { return $this->hasMany(Note::class); }`. This allows you to easily get a user's notes by typing `$user->notes`.
* **Soft Deletes**: By adding the `SoftDeletes` trait to the `Note` model, calling `$note->delete()` simply fills in the `deleted_at` timestamp instead of erasing the row. Eloquent automatically hides these "trashed" notes from all future database queries.

---

## 4. Controllers (`app/Http/Controllers/`)
The controller handles the logic. It receives the HTTP request, asks the Model for data, and returns a response. You have two `NoteController` files: one for the web UI and one for the API.
* **`NoteController.php`**: Returns `Inertia::render(...)` to display a full React page.
* **`Api/NoteController.php`**: Returns `response()->json(...)` to send raw data to an API client.

---

## 5. Validation: Form Requests (`app/Http/Requests/`)
Instead of cluttering the Controller with validation rules, we extracted them into dedicated Form Request classes (`StoreNoteRequest` and `UpdateNoteRequest`). When the controller method receives one of these requests, Laravel automatically validates the incoming data *before* the controller code even runs.

---

## 6. Security: Policies & API Authentication

### Policies (`app/Policies/NotePolicy.php`)
Policies define **Authorization**—who is allowed to do what.
```php
public function update(User $user, Note $note): bool
{
    return $user->id === $note->user_id;
}
```
This ensures a user can only update or delete a note if they are the true owner. We trigger this check in the controller using `Gate::authorize('delete', $note)`.

### API Authentication (Sanctum)
For our API, we use **Laravel Sanctum**.
1. A user sends their email/password to the `POST /api/login` endpoint.
2. The `LoginController` verifies their credentials.
3. If valid, it generates a token using `$user->createToken()` and returns it. The `User` model must have the `HasApiTokens` trait for this to work.
4. For all subsequent requests, the client must include an `Authorization` header with that token (e.g., `Authorization: Bearer <token>`).
5. The `auth:sanctum` middleware on your API routes automatically validates this token on every request.

---

## 7. Automated Testing (`tests/`)
Automated testing is the practice of writing code to test your application. This gives you the confidence to make changes without manually checking if you broke something. Laravel uses a testing framework called **Pest**.

### The "Arrange, Act, Assert" Pattern
This is the fundamental structure of a good test.
* **Arrange**: Set up the world for your test. Create the necessary users, notes, or any other data in the database.
* **Act**: Perform the action you want to test. This is usually making an API request.
* **Assert**: Check that the result is what you expected. Did you get the right status code? Is the JSON response correct? Was the database updated?

### Example: API Feature Test (`tests/Feature/Api/NoteApiTest.php`)
This file tests your API from the "outside-in", just like Postman would.

```php
public function test_user_can_update_their_own_note(): void
{
    // 1. ARRANGE: Create a user and a note for them to own.
    $user = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $user->id]);
    $updatedData = ['title' => 'Updated Title'];

    // 2. ACT: Authenticate as the user and send a PUT request with the new data.
    $response = $this->actingAs($user, 'sanctum')->putJson('/api/notes/' . $note->id, $updatedData);

    // 3. ASSERT: Check the results.
    $response->assertStatus(200); // Check for a 200 OK status
    $this->assertDatabaseHas('notes', [ // Check that the database was actually changed
        'id' => $note->id,
        'title' => 'Updated Title'
    ]);
}
```
* **`RefreshDatabase` trait**: This useful trait automatically wipes the database clean before each test, ensuring your tests are isolated from each other.
* **`actingAs($user, 'sanctum')`**: This helper simulates a user being logged in via Sanctum for an API request.
* **`putJson($uri, $data)`**: Sends a `PUT` request with a JSON body.
* **`assertStatus(200)`**: Checks the HTTP response code.
* **`assertDatabaseHas(...)`**: Checks the database directly to confirm a record exists with the given data.

To run all your tests, you simply run `./vendor/bin/pest` from your terminal.

---

## 8. Search and Filtering
We implemented a live search bar to filter the notes. This involved changes to both the backend and frontend.

### Backend: Conditional Database Queries
In the `NoteController@index` method, we modified the query to be conditional.
```php
public function index(Request $request)
{
    $notes = Auth::user()->notes()
        ->when($request->input('search'), function ($query, $search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return Inertia::render('Notes/Index', [
        'notes' => $notes,
        'filters' => $request->only(['search']),
    ]);
}
```
* **`->when(...)`**: This is a clean way to build a query. The code inside the closure will only run if the first argument (`$request->input('search')`) is not empty.
* **`->withQueryString()`**: This is a crucial helper method that tells the paginator to append the current query string (e.g., `?search=hello`) to all the pagination links it generates. This ensures that your search filter is not lost when you navigate to the next page.
* **`'filters' => ...`**: We pass the search term back to the frontend so that the search bar can be pre-filled with the current search value.

### Frontend: State Management and Debouncing
In `resources/js/Pages/Notes/Index.jsx`, we used React hooks to create an interactive search experience.
```jsx
// 1. Keep track of the input's value in state
const [searchTerm, setSearchTerm] = useState(filters.search || '');

// 2. Use a debounced function to avoid sending too many requests
const debouncedSearch = useCallback(
    debounce((nextValue) => {
        router.get(route('notes.index'), { search: nextValue }, {
            preserveState: true,
            replace: true,
        });
    }, 300),
    []
);

// 3. Watch for changes and call the debounced function
useEffect(() => {
    if (searchTerm !== filters.search) {
        debouncedSearch(searchTerm);
    }
}, [searchTerm, filters.search, debouncedSearch]);
```
* **`useState`**: We use a state variable, `searchTerm`, to hold the current text inside the search bar.
* **`useEffect`**: This hook "watches" the `searchTerm` variable. Whenever the user types and `searchTerm` changes, the code inside the hook runs.
* **`debounce`**: This is a critical performance optimization from the `lodash` library. Instead of firing a new network request on every single keystroke, it waits until the user has **stopped typing for 300ms**. This feels responsive to the user but prevents overwhelming your server with unnecessary requests.
* **`router.get(...)`**: This is Inertia's function for making a partial request to the server. It re-fetches the data from the `NoteController` with the new search query parameter and seamlessly updates the `notes` prop on the page without a full page reload.

---

## 9. Soft Deletes & Trash Can UI
We implemented a complete "Trash Can" feature, allowing users to restore or permanently delete soft-deleted notes.

### Backend: Advanced Eloquent Queries
We added new routes and methods to handle trashed notes:
* **`onlyTrashed()`**: Used in the `trash` method to retrieve *only* notes that have a `deleted_at` timestamp.
  ```php
  $trashedNotes = Auth::user()->notes()->onlyTrashed()->paginate(10);
  ```
* **`withTrashed()`**: Used in the `restore` and `forceDelete` methods. Default Route Model Binding ignores soft-deleted items, so we must manually find the note, explicitly telling Eloquent to include trashed items in its search.
  ```php
  $note = Note::withTrashed()->findOrFail($id);
  ```

### Security & Policies
We updated the `NotePolicy` to include `restore` and `forceDelete` methods, ensuring a user can only perform these actions on their own notes. We enforce this in the controller using `Gate::authorize('restore', $note)`.

### Frontend
We created a new `Trash.jsx` component that displays the trashed notes. Instead of standard "Edit" and "Delete" actions, it provides:
* **Restore**: Sends a `PUT` request to the restore route.
* **Force Delete**: Sends a `DELETE` request to the force-delete route, which permanently removes the record from the database.

---

## 10. Automated Testing with CI/CD (GitHub Actions)
To ensure the application remains stable as new features are added, we set up Continuous Integration (CI) using GitHub Actions.

### What is CI/CD?
Continuous Integration is the practice of automatically running your automated tests every time new code is pushed to your repository. This guarantees that your `main` and `develop` branches are always in a working state.

### The Workflow File (`.github/workflows/laravel.yml`)
GitHub Actions are defined using YAML files. Our workflow instructs GitHub's servers to automatically perform the following steps whenever code is pushed:

1. **Trigger**: The `on: push` section tells the workflow to run when changes are pushed to `main`, `test`, or `develop`.
2. **Environment Setup**: It provisions a virtual machine (`ubuntu-latest`) and installs PHP 8.4 using `shivammathur/setup-php`.
3. **Install Dependencies**: It runs `composer install` and `npm install` to gather all backend and frontend packages.
4. **Build Assets**: It runs `npm run build` so Vite compiles the frontend assets, which prevents "manifest not found" errors during testing.
5. **Database Prep**: It copies the `.env.example` file and creates an empty SQLite database file (`touch database/database.sqlite`) to satisfy Laravel's default configuration before migrations run.
6. **Run Migrations & Tests**: Finally, it runs `php artisan migrate --force` followed by `php artisan test`.

### In-Memory SQLite Testing (`phpunit.xml`)
To make the tests run as fast and reliably as possible, we configured `phpunit.xml` to use an **in-memory SQLite database**:
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```
This ensures the tests don't modify your local development database and run entirely in RAM, wiping clean after every test run.

---

## 11. User Roles & Permissions
We implemented a role-based authorization system to distinguish between regular users and administrators.

### Database & Model
1.  **Migration**: We added a `role` string column to the `users` table, with a default value of `'user'`.
2.  **User Model**: We added a helper method `public function isAdmin(): bool` to easily check if a user's role is `'admin'`.
3.  **User Factory**: We added an `admin()` state to the factory, allowing us to create admin users for testing via `User::factory()->admin()->create()`.

### Authorization
Instead of adding `if ($user->isAdmin())` to every method in our `NotePolicy`, we used the `before()` method. This special method runs before any other policy check. If it returns `true`, the user is immediately authorized, granting admins "superuser" access to all notes.
```php
// app/Policies/NotePolicy.php
public function before(User $user, string $ability): bool|null
{
    if ($user->isAdmin()) {
        return true;
    }
    return null; // Let the regular policy method decide
}
```

### Middleware & Routing
1.  **Middleware**: We created a new middleware at `app/Http/Middleware/EnsureUserIsAdmin.php`. This middleware checks if the logged-in user is an admin. If not, it aborts the request with a `403 Forbidden` error.
2.  **Registering Middleware**: We registered this middleware in `bootstrap/app.php` with the convenient alias `'admin'`.
3.  **Protected Routes**: We created a new route group in `routes/web.php` that uses the middleware to protect all routes within it, creating an admin-only section.
    ```php
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    });
    ```

### Conditional UI
In the `AuthenticatedLayout.jsx` component, we now check the user's role and conditionally render the "Admin" navigation link, ensuring that only administrators can see it.
```jsx
const isAdmin = user && user.role === 'admin';
// ...
{isAdmin && (
    <NavLink href={route('admin.index')}>Admin</NavLink>
)}
```

---

### Keep Learning!
If you want to keep exploring, try adding these features next:
1. **Admin User Management**: Build a page in the admin section to list all users and manage their roles.
2. **Sharing Notes**: Allow users to share their notes with other users.
3. **Rich Text Editor**: Replace the plain textareas with a modern rich text editor like Tiptap.
