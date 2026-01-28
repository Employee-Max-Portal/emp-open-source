# Contributing to EMP

We welcome contributions from the employee management development community. EMP (Employee Max Portal) is developed by [SOHUB (Solution Hub Technologies)](https://sohub.com.bd/) and is designed to serve organizations with complex employee management requirements, and we value contributions that maintain this focus.

## Code of Conduct

All contributors must adhere to our code of conduct:
- Maintain professional communication in all interactions
- Respect diverse perspectives and experiences
- Focus on constructive feedback and solutions
- Prioritize employee management security and stability requirements

## Getting Started

### Development Environment Setup

1. **Fork and Clone**
```bash
git clone https://github.com/yourusername/emp-opensource.git
cd emp-opensource
git remote add upstream https://github.com/original/emp-opensource.git
```

2. **Local Development Setup**
```bash
# Install dependencies
composer install

# Set up development database
cp application/config/database.php.example application/config/database.php
# Configure your local database credentials

# Import development schema
mysql -u username -p emp_dev < database/schema/emp_core.sql
```

3. **Development Standards**
- Follow PSR-2 coding standards
- Use meaningful commit messages
- Include unit tests for new features
- Update documentation for changes

## Contribution Types

### Bug Reports

**Before Submitting**
- Search existing issues to avoid duplicates
- Test with the latest version
- Gather system information and error logs

**Bug Report Template**
```markdown
**Environment**
- EMP Version: 
- PHP Version: 
- MySQL Version: 
- Web Server: 

**Description**
Clear description of the bug

**Steps to Reproduce**
1. Step one
2. Step two
3. Step three

**Expected Behavior**
What should happen

**Actual Behavior**
What actually happens

**Additional Context**
Logs, screenshots, or other relevant information
```

### Feature Requests

**Feature Request Guidelines**
- Align with enterprise use cases
- Consider security and scalability implications
- Provide detailed use case scenarios
- Include implementation considerations

### Code Contributions

**Pull Request Process**

1. **Create Feature Branch**
```bash
git checkout -b feature/your-feature-name
```

2. **Development Guidelines**
- Write clean, documented code
- Follow existing code patterns
- Include comprehensive tests
- Update relevant documentation

3. **Testing Requirements**
```bash
# Run unit tests
phpunit tests/unit/

# Run integration tests
phpunit tests/integration/

# Check code quality
phpcs --standard=PSR2 application/
```

4. **Commit Standards**
```bash
# Use conventional commit format
git commit -m "feat: add employee bulk import functionality"
git commit -m "fix: resolve attendance calculation bug"
git commit -m "docs: update API documentation"
```

## Development Standards

### Coding Standards

**PHP Standards**
- Follow PSR-2 coding style
- Use meaningful variable and function names
- Include comprehensive PHPDoc comments
- Implement proper error handling

**Database Standards**
- Use CodeIgniter Query Builder
- Implement proper indexing
- Follow naming conventions
- Include migration scripts

**Frontend Standards**
- Maintain responsive design principles
- Follow Bootstrap conventions
- Implement progressive enhancement
- Ensure accessibility compliance

### Security Requirements

**Security Checklist**
- [ ] Input validation and sanitization
- [ ] Output encoding for XSS prevention
- [ ] CSRF protection implementation
- [ ] SQL injection prevention
- [ ] Proper authentication and authorization
- [ ] Secure file handling
- [ ] Audit logging for sensitive operations

### Testing Requirements

**Test Coverage**
- Unit tests for models and libraries
- Integration tests for controllers
- API endpoint testing
- Security testing for new features

**Test Structure**
```php
class EmployeeModelTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->load_model('employee_model');
    }

    public function test_create_employee_success()
    {
        $employee_data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'department_id' => 1
        ];
        
        $result = $this->employee_model->create_employee($employee_data);
        $this->assertIsNumeric($result);
    }
}
```

## Documentation Standards

### Code Documentation

**PHPDoc Standards**
```php
/**
 * Calculate employee salary based on attendance and deductions
 *
 * @param int $employee_id Employee ID
 * @param string $month Salary month (YYYY-MM format)
 * @param array $deductions Optional deductions array
 * @return array Salary calculation details
 * @throws InvalidArgumentException If employee not found
 */
public function calculate_salary($employee_id, $month, $deductions = [])
{
    // Implementation
}
```

### API Documentation

**Endpoint Documentation**
```php
/**
 * @api {post} /api/v1/employees Create Employee
 * @apiName CreateEmployee
 * @apiGroup Employee
 * @apiVersion 1.0.0
 *
 * @apiParam {String} name Employee full name
 * @apiParam {String} email Employee email address
 * @apiParam {Number} department_id Department ID
 *
 * @apiSuccess {Number} employee_id Created employee ID
 * @apiSuccess {String} message Success message
 *
 * @apiError {String} error Error message
 */
```

## Review Process

### Code Review Guidelines

**Review Criteria**
- Code quality and maintainability
- Security implementation
- Performance considerations
- Documentation completeness
- Test coverage adequacy

**Review Process**
1. Automated checks (CI/CD pipeline)
2. Security review for sensitive changes
3. Technical review by maintainers
4. Final approval and merge

### Merge Requirements

**Before Merge**
- [ ] All tests passing
- [ ] Code review approved
- [ ] Documentation updated
- [ ] Security review completed (if applicable)
- [ ] Backward compatibility maintained

## Release Process

### Version Management

**Semantic Versioning**
- MAJOR: Breaking changes
- MINOR: New features (backward compatible)
- PATCH: Bug fixes (backward compatible)

**Release Branches**
```bash
# Create release branch
git checkout -b release/v1.2.0

# Prepare release
# Update version numbers
# Update CHANGELOG.md
# Final testing

# Merge to main
git checkout main
git merge release/v1.2.0
git tag v1.2.0
```

### Changelog Management

**Changelog Format**
```markdown
## [1.2.0] - 2024-01-15

### Added
- Employee bulk import functionality
- Advanced reporting dashboard
- Multi-language support for notifications

### Changed
- Improved attendance calculation performance
- Updated user interface for mobile devices

### Fixed
- Resolved payroll calculation edge cases
- Fixed session timeout issues

### Security
- Enhanced input validation
- Updated dependency versions
```

## Community Guidelines

### Communication Channels

**GitHub Discussions**
- Feature discussions and proposals
- Implementation questions
- Community support

**GitHub Issues**
- Bug reports and tracking
- Feature requests
- Security vulnerability reports

### Support Guidelines

**Getting Help**
1. Check existing documentation
2. Search GitHub issues and discussions
3. Create detailed issue or discussion post
4. Provide complete context and examples

**Providing Help**
- Be respectful and constructive
- Provide clear, actionable guidance
- Share relevant documentation links
- Follow up on solutions

## Recognition

### Contributor Recognition

**Contribution Types**
- Code contributions
- Documentation improvements
- Bug reports and testing
- Community support and mentoring

**Recognition Methods**
- Contributor listing in README
- Release notes acknowledgments
- Community spotlight features
- Maintainer invitation for significant contributors

## Legal Requirements

### Licensing

**MIT License Compliance**
- All contributions licensed under MIT License
- No proprietary code inclusion
- Respect third-party license requirements

### Intellectual Property

**Contribution Agreement**
By contributing, you agree that:
- Your contributions are your original work
- You have rights to contribute the code
- Contributions are licensed under project license
- You understand the open source nature of contributions

---

Thank you for contributing to EMP! Your contributions help build a better enterprise management platform for organizations worldwide.