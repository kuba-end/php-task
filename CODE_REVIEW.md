# Code Review: Payroll System

## Executive Summary

This code review focuses on architectural concerns related to **CQRS**, **DDD**, and **SOLID** principles. The codebase shows good separation of concerns with layered architecture, but there are several violations that need to be addressed to improve maintainability, testability, and scalability.

---

## 1. CQRS (Command Query Responsibility Segregation) Issues

### 🔴 Critical: Repository Interface Depends on Application Layer

**Location:** `src/Domain/Repository/EmployeeRepositoryInterface.php`

**Problem:**
```php
public function findAllFilteredAndSorted(array $filters, ?array $sort, SortResolver $resolver): array;
```

The Domain layer repository interface depends on `SortResolver` from the Application layer. This violates the Dependency Rule - Domain should not depend on Application.

**Impact:**
- Breaks dependency inversion principle
- Makes Domain layer tightly coupled to Application layer
- Difficult to test Domain layer in isolation

**Recommendation:**
- Move sorting/filtering logic to Application layer
- Repository should only accept simple parameters (arrays, primitives)
- Create a Specification pattern or Filter DTOs in Domain layer
- Or use a Query Builder pattern in Application layer

### 🟡 Medium: Query Handler Doing Too Much

**Location:** `src/Application/Payroll/Query/GetPayrollQueryHandler.php`

**Problem:**
The query handler is responsible for:
1. Parsing and validating sort parameters
2. Fetching employees from repository
3. Calculating remuneration for each employee
4. Transforming entities to DTOs
5. Performing in-memory sorting

**Impact:**
- Violates Single Responsibility Principle
- Hard to test individual concerns
- Difficult to optimize (e.g., caching, pagination)

**Recommendation:**
- Split into smaller, focused services:
  - `SortParameterParser` - parse and validate sort
  - `EmployeeQueryService` - fetch employees
  - `PayrollCalculationService` - calculate payroll
  - `PayrollReadModelBuilder` - build read model

### 🟡 Medium: Mixed Sorting Strategy (DB + Memory)

**Location:** `src/Application/Payroll/Query/GetPayrollQueryHandler.php:47-55`

**Problem:**
Sorting is partially done in the database and partially in memory. This creates:
- Inconsistent performance characteristics
- Complex logic to determine where sorting happens
- Potential for bugs when adding new sortable fields

**Recommendation:**
- **Option 1:** Use a Read Model (CQRS pattern) - pre-calculate all payroll data and store in a denormalized table
- **Option 2:** Always sort in memory after calculation (simpler, but less performant)
- **Option 3:** Use database views or materialized views for calculated fields

---

## 2. DDD (Domain-Driven Design) Issues

### 🔴 Critical: Domain Entities Have Infrastructure Dependencies

**Location:** `src/Domain/Entity/Employee.php:11`, `src/Domain/Entity/Department.php:12`

**Problem:**
```php
#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
```

Domain entities reference infrastructure-specific repository classes. This violates the Dependency Rule.

**Impact:**
- Domain layer is coupled to Doctrine ORM
- Cannot easily swap persistence mechanisms
- Domain layer should be framework-agnostic

**Recommendation:**
- Remove `repositoryClass` from ORM attributes
- Configure repositories in infrastructure configuration
- Use Doctrine's default repository resolution

### 🟡 Medium: Anemic Domain Model

**Location:** `src/Domain/Entity/Employee.php`, `src/Domain/Entity/Department.php`

**Problem:**
Entities are pure data holders with getters/setters. No business logic, no invariants protection.

**Impact:**
- Business logic scattered across application layer
- No encapsulation of domain rules
- Easy to create invalid entity states

**Recommendation:**
- Add domain methods (e.g., `Employee::calculateRemuneration()`)
- Add invariants and validation in constructors/setters
- Use Value Objects for complex types (e.g., `Remuneration`, `YearsOfWork`)

### 🟡 Medium: Missing Domain Services

**Location:** Business logic in `RemunerationCalculator`

**Observation:**
The calculator is well-designed with Strategy pattern, but it's in the Domain layer while being used by Application layer. This is actually correct, but consider:
- Should calculation be a Domain Service or Application Service?
- Currently it's a Domain Service (good), but ensure it doesn't depend on infrastructure

### 🟢 Good: Exception Handling

**Location:** `src/Domain/Exception/InvalidSortingException.php`

The domain exceptions are properly placed in the Domain layer.

---

## 3. SOLID Principles Issues

### 🔴 Critical: Dependency Inversion Violation

**Location:** `src/Domain/Repository/EmployeeRepositoryInterface.php`

**Problem:**
Domain interface depends on Application layer (`SortResolver`).

**Fix:**
```php
// Instead of:
public function findAllFilteredAndSorted(array $filters, ?array $sort, SortResolver $resolver): array;

// Use:
public function findAll(EmployeeSpecification $specification): array;
// Or:
public function findAll(FilterCriteria $filters, SortCriteria $sort): array;
```

### 🟡 Medium: Single Responsibility Violation

**Location:** `src/Application/Payroll/Query/SortResolver.php`

**Problem:**
`SortResolver` has multiple responsibilities:
1. Parsing sort strings
2. Validating sortable fields
3. Mapping fields to DB columns
4. Determining if field is DB or memory sortable

**Recommendation:**
Split into:
- `SortParameterParser` - parse string to array
- `SortFieldValidator` - validate field names
- `SortFieldMapper` - map fields to DB columns
- `SortStrategyResolver` - determine DB vs memory sorting

### 🟡 Medium: Interface Segregation Violation

**Location:** `src/Domain/Repository/EmployeeRepositoryInterface.php`

**Problem:**
The interface forces implementers to handle complex filtering and sorting logic, even if they don't need it.

**Recommendation:**
```php
interface EmployeeRepositoryInterface
{
    public function findAll(): array;
    public function findBySpecification(Specification $spec): array;
}

// Or use a query builder pattern:
interface EmployeeQueryBuilder
{
    public function withFilters(array $filters): self;
    public function withSorting(array $sort): self;
    public function getResults(): array;
}
```

### 🟡 Medium: Open/Closed Principle

**Location:** Filtering and sorting logic

**Problem:**
Adding new filters or sortable fields requires modifying existing code in multiple places:
- `SortResolver` constants
- Repository implementation
- Query handler

**Recommendation:**
- Use Strategy pattern for filters
- Use a registry for sortable fields
- Use Specification pattern for complex queries

---

## 4. Architectural Improvements

### 🟡 Medium: Missing Read Model (CQRS)

**Current State:**
- Query reads from write model (Employee entity)
- Calculates payroll on-the-fly
- Mixed DB/memory sorting

**Recommendation:**
For better CQRS implementation:
1. Create a `PayrollReadModel` table/view
2. Update it via domain events when employees/departments change
3. Query handler reads directly from read model
4. All sorting/filtering happens in database

**Benefits:**
- Better performance
- Simpler query logic
- Clear separation of read/write models

### 🟡 Medium: Filter Abstraction

**Location:** `src/Infrastructure/Repository/EmployeeRepository.php:31-44`

**Problem:**
Filtering logic is hardcoded in repository with if statements.

**Recommendation:**
Use Specification pattern:
```php
interface EmployeeSpecification
{
    public function isSatisfiedBy(Employee $employee): bool;
    public function applyToQuery(QueryBuilder $qb): void;
}

class DepartmentFilterSpecification implements EmployeeSpecification { }
class NameFilterSpecification implements EmployeeSpecification { }
```

### 🟡 Medium: PayrollReportItem Encapsulation

**Location:** `src/Application/Payroll/Query/PayrollReportItem.php:25-32`

**Problem:**
The `get()` method uses reflection-like access and breaks encapsulation. Used in sorting:
```php
$a->{$field} <=> $b->{$field}
```

**Recommendation:**
- Use explicit getter methods
- Or create a `Comparable` interface
- Or use a Value Object with comparison methods

### 🟢 Good: Strategy Pattern for Bonus Calculation

**Location:** `src/Domain/Employee/Calculator/Strategy/`

The bonus calculation uses Strategy pattern correctly. Well done!

---

## 5. Specific Code Issues

### 🟡 Medium: Type Safety in PayrollReportItem

**Location:** `src/Application/Payroll/Query/PayrollReportItem.php:25-32`

**Problem:**
The `get()` method returns `mixed` and uses dynamic property access.

**Recommendation:**
```php
public function getFieldValue(string $field): mixed
{
    return match($field) {
        'name' => $this->name,
        'surname' => $this->surname,
        'department' => $this->department,
        // ... etc
        default => throw new \InvalidArgumentException("Unknown field: $field")
    };
}
```

### 🟡 Medium: Error Handling

**Location:** `src/Infrastructure/Listener/QueryExceptionListener.php`

**Problem:**
Generic 500 error for all non-handled exceptions. Should handle more domain exceptions.

**Recommendation:**
- Create exception mapping configuration
- Handle `StrategyNotFoundException` properly
- Add proper logging

### 🟢 Good: Use of Value Objects

**Observation:**
Using `Uuid` from Symfony is good. Consider creating domain-specific value objects for:
- `EmployeeId`
- `DepartmentId`
- `Remuneration` (with validation)
- `YearsOfWork` (with validation)

---

## 6. Recommendations Summary

### High Priority (Critical)

1. **Remove Domain → Application dependency**
   - Refactor `EmployeeRepositoryInterface` to not depend on `SortResolver`
   - Move sorting/filtering logic to Application layer

2. **Remove Infrastructure references from Domain entities**
   - Remove `repositoryClass` from ORM attributes
   - Configure repositories in infrastructure config

### Medium Priority

3. **Split Query Handler responsibilities**
   - Extract parsing, validation, calculation, and transformation into separate services

4. **Implement Specification pattern for filtering**
   - Replace if-statements with composable specifications

5. **Consider Read Model for CQRS**
   - Create denormalized payroll table
   - Update via domain events

6. **Improve encapsulation**
   - Replace dynamic property access in `PayrollReportItem`
   - Add proper getter methods or use match expression

### Low Priority

7. **Add domain methods to entities**
   - Move business logic from Application to Domain
   - Protect invariants

8. **Create Value Objects**
   - For IDs, Remuneration, YearsOfWork

9. **Improve error handling**
   - Map all domain exceptions to HTTP responses
   - Add proper logging

---

## 7. Positive Aspects

✅ Good layered architecture (Domain, Application, Infrastructure, Presentation)  
✅ Proper use of Strategy pattern for bonus calculation  
✅ Clean separation of concerns in most areas  
✅ Good use of Symfony Messenger for CQRS queries  
✅ Proper exception hierarchy in Domain layer  
✅ Type hints and strict types throughout  
✅ Good naming conventions  

---

## Conclusion

The codebase shows a solid understanding of modern PHP architecture, but there are several violations of CQRS, DDD, and SOLID principles that should be addressed. The most critical issues are:

1. Domain layer depending on Application layer
2. Infrastructure dependencies in Domain entities
3. Query handler doing too much

Addressing these will significantly improve the maintainability and testability of the codebase.

