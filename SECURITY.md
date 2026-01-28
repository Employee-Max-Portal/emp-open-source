# Security Policy

## Supported Versions

We actively support the following versions of EMP with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 2.x.x   | :white_check_mark: |
| 1.8.x   | :white_check_mark: |
| 1.7.x   | :x:                |
| < 1.7   | :x:                |

## Reporting a Vulnerability

We take security vulnerabilities seriously and appreciate responsible disclosure. If you discover a security vulnerability in EMP, please follow these guidelines:

### Reporting Process

1. **Do NOT** create a public GitHub issue for security vulnerabilities
2. **Do NOT** discuss the vulnerability publicly until it has been addressed
3. **Do** report the vulnerability privately using one of these methods:

**Preferred Method: GitHub Security Advisories**
- Go to the repository's Security tab
- Click "Report a vulnerability"
- Fill out the vulnerability report form

**Alternative Method: Email**
- Send details to: security@emp-portal.com
- Use PGP encryption if possible (key available on request)

### Information to Include

Please provide as much information as possible:

- **Vulnerability Type**: What type of vulnerability (XSS, SQL injection, etc.)
- **Location**: Affected files, URLs, or components
- **Impact**: Potential impact and severity assessment
- **Reproduction Steps**: Detailed steps to reproduce the vulnerability
- **Proof of Concept**: Code or screenshots demonstrating the issue
- **Suggested Fix**: If you have ideas for remediation

### Example Report Format

```
Subject: [SECURITY] SQL Injection in Employee Search

Vulnerability Type: SQL Injection
Affected Component: Employee Management Module
Affected File: application/controllers/Employee.php (line 45)
Severity: High

Description:
The employee search functionality is vulnerable to SQL injection through the 'search_term' parameter.

Reproduction Steps:
1. Navigate to /employee/search
2. Enter the following payload in search field: ' OR 1=1 --
3. Observe that all employee records are returned

Impact:
An attacker could potentially:
- Extract sensitive employee data
- Modify database records
- Gain unauthorized access to system information

Suggested Fix:
Use parameterized queries or CodeIgniter's Query Builder for database operations.
```

## Response Timeline

We are committed to addressing security vulnerabilities promptly:

- **Initial Response**: Within 48 hours of report
- **Vulnerability Assessment**: Within 5 business days
- **Fix Development**: Based on severity (Critical: 7 days, High: 14 days, Medium: 30 days)
- **Security Release**: As soon as fix is tested and validated
- **Public Disclosure**: 90 days after fix release (or sooner with reporter agreement)

## Severity Classification

We use the following severity levels:

### Critical
- Remote code execution
- SQL injection with data access
- Authentication bypass
- Privilege escalation to admin

### High
- Cross-site scripting (XSS) with session hijacking
- Local file inclusion/disclosure
- Significant data exposure
- CSRF with high impact

### Medium
- Information disclosure (limited)
- Denial of service
- CSRF with medium impact
- Weak cryptographic implementation

### Low
- Minor information disclosure
- Security misconfigurations
- Issues requiring user interaction

## Security Measures

EMP implements multiple security layers:

### Application Security
- Input validation and sanitization
- Output encoding for XSS prevention
- CSRF protection on all forms
- SQL injection prevention through parameterized queries
- Secure session management
- Role-based access control (RBAC)

### Infrastructure Security
- HTTPS enforcement
- Secure cookie configuration
- Security headers implementation
- File upload restrictions
- Rate limiting and abuse protection

### Data Protection
- Encryption at rest for sensitive data
- Secure password hashing (bcrypt)
- Audit logging for sensitive operations
- Regular security updates and patches

## Security Best Practices for Users

### For System Administrators
- Keep EMP updated to the latest version
- Use strong, unique passwords for all accounts
- Enable HTTPS with valid SSL certificates
- Regularly review user permissions and access logs
- Implement proper backup and recovery procedures
- Monitor system logs for suspicious activities

### For Developers
- Follow secure coding practices
- Validate all user inputs
- Use parameterized queries for database operations
- Implement proper error handling
- Keep dependencies updated
- Conduct security reviews for code changes

## Acknowledgments

We appreciate the security research community and will acknowledge researchers who responsibly disclose vulnerabilities:

- Security researchers will be credited in release notes (with permission)
- Significant vulnerabilities may be eligible for recognition in our Hall of Fame
- We support coordinated disclosure and will work with researchers on timing

## Contact Information

For security-related questions or concerns:

- **Security Team**: security@sohub.com.bd
- **General Contact**: GitHub Issues (for non-security matters)
- **Company Website**: [https://sohub.com.bd/](https://sohub.com.bd/)
- **Documentation**: See `/docs/security.md` for detailed security implementation

## Legal

This security policy is provided in good faith. We request that security researchers:

- Act in good faith and avoid privacy violations or data destruction
- Provide reasonable time for vulnerability remediation
- Do not access or modify data beyond what is necessary to demonstrate the vulnerability
- Comply with applicable laws and regulations

We commit to:

- Respond to vulnerability reports in a timely manner
- Not pursue legal action against researchers who follow this policy
- Work collaboratively to resolve security issues
- Provide credit for responsible disclosure (with permission)

---

Thank you for helping keep EMP and our users secure!