### PHP Classes for JavaScript Developers

PHP and JavaScript use different syntax for Object-Oriented Programming (OOP), but the core logic remains the same. In PHP, classes are the blueprints for objects, utilizing properties for data and methods for behavior.

### Basic Syntax Comparison

In JavaScript, you often use `this` and a constructor function. In PHP, you use the `->` operator to access members and `__construct` as the constructor method.

#### JavaScript Example

```javascript
class User {
    constructor(name) {
        this.name = name;
    }

    sayHello() {
        console.log(`Hello, ${this.name}`);
    }
}
```

#### PHP Example

```php
class User
{
    // Properties must be declared first
    public $name;

    // Constructor method
    public function __construct($name)
    {
        $this->name = name;
    }

    // Method
    public function sayHello()
    {
        echo 'Hello, ' . $this->name;
    }
}

// Instantiation
$user = new User('Alice');
$user->sayHello();
```

---

### Key Differences to Note

1. **Property Declaration**: PHP requires you to declare properties (variables) inside the class body, usually with a visibility keyword like `public`. JavaScript allows dynamic assignment in the constructor.
2. **The Arrow Operator**: PHP uses `->` to access properties and methods (e.g., `$this->name`). JavaScript uses the dot `.` operator (e.g., `this.name`).
3. **Variable Sigils**: You must use the `$` for variables, but when accessing a property via `$this->`, the property name itself does not use a `$`.

- Correct: `$this->name`
- Incorrect: `$this->$name`

4. **Constructors**: PHP uses the magic method `__construct()`. JavaScript uses `constructor()`.

---

### Visibility (Access Modifiers)

PHP offers explicit control over who can see class members. JavaScript recently added private fields using `#`, but PHP uses keywords:

- **public**: Accessible from anywhere.
- **private**: Accessible only within the class itself.
- **protected**: Accessible within the class and its children (inheritance).

#### Example with Encapsulation

```php
class BankAccount
{
    private $balance;

    public function __construct($amount)
    {
        $this->balance = $amount;
    }

    public function getBalance()
    {
        return "$" . $this->balance;
    }
}

$account = new BankAccount(100);
echo $account->getBalance(); // Works
// echo $account->balance;   // Throws an error because it is private
```

---

### Inheritance

Both languages use the `extends` keyword. To call a parent method in PHP, use `parent::` instead of `super()`.

```php
class Animal
{
    public function eat()
    {
        echo 'Eating...';
    }
}

class Dog extends Animal
{
    public function bark()
    {
        echo 'Woof!';
    }
}

$dog = new Dog();
$dog->eat(); // Inherited
$dog->bark(); // Own method
```
