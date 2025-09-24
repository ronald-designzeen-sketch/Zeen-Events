# Contributing to Zeen Events

Thank you for your interest in contributing to Zeen Events! This document provides guidelines and information for contributors.

## 🚀 Getting Started

### Prerequisites
- WordPress 5.0+
- PHP 7.4+
- Git
- A local WordPress development environment

### Development Setup

1. **Fork the repository**
   ```bash
   git clone https://github.com/your-username/Zeen-Events.git
   cd Zeen-Events
   ```

2. **Set up your development environment**
   - Install WordPress locally
   - Copy the plugin to `/wp-content/plugins/zeen-events/`
   - Activate the plugin

3. **Install dependencies** (if using build tools)
   ```bash
   npm install
   ```

## 📝 Development Guidelines

### Code Standards
- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- Use PSR-4 autoloading where applicable
- Write clean, readable, and well-documented code
- Follow the existing code structure and patterns

### Architecture Principles
- **Single Responsibility**: Each class should have one clear purpose
- **Clean Data Flow**: Data → Service → Renderer pattern
- **Simple Functions**: No function should exceed 50 lines
- **Consistent Patterns**: Use the same approach for similar functionality

### File Structure
```
zeen-events/
├── zeen-events.php              # Main plugin file
├── includes/
│   ├── core.php                 # Core architecture
│   ├── database.php             # Database management
│   ├── rest-api.php             # REST API endpoints
│   ├── performance-optimizer.php # Performance optimizations
│   ├── security-manager.php     # Security features
│   ├── analytics-engine.php     # Analytics system
│   ├── admin-dashboard.php      # Admin dashboard
│   ├── registration-system.php  # Event registration
│   ├── payment-gateways.php     # Payment processing
│   └── multisite-support.php    # Multi-site features
├── assets/
│   ├── css/                     # Stylesheets
│   └── js/                      # JavaScript files
├── templates/                   # Template files
└── tests/                       # Unit tests
```

## 🐛 Bug Reports

### Before Submitting
1. Check if the issue already exists
2. Test with the latest version
3. Disable other plugins to isolate the issue

### Bug Report Template
```markdown
**Bug Description**
A clear description of the bug.

**Steps to Reproduce**
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected Behavior**
What you expected to happen.

**Actual Behavior**
What actually happened.

**Environment**
- WordPress Version: 
- PHP Version: 
- Plugin Version: 
- Browser: 

**Screenshots**
If applicable, add screenshots.

**Additional Context**
Any other context about the problem.
```

## ✨ Feature Requests

### Before Submitting
1. Check if the feature already exists
2. Consider if it aligns with the plugin's goals
3. Think about the implementation complexity

### Feature Request Template
```markdown
**Feature Description**
A clear description of the feature.

**Use Case**
Why would this feature be useful?

**Proposed Solution**
How would you like this feature to work?

**Alternatives Considered**
Any alternative solutions you've considered.

**Additional Context**
Any other context about the feature request.
```

## 🔧 Pull Requests

### Before Submitting
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes
4. Test thoroughly
5. Update documentation if needed

### Pull Request Template
```markdown
**Description**
Brief description of changes.

**Type of Change**
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

**Testing**
- [ ] Tested locally
- [ ] All tests pass
- [ ] No new warnings

**Checklist**
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No breaking changes
```

### Code Review Process
1. Automated tests must pass
2. Code review by maintainers
3. Manual testing
4. Documentation review

## 🧪 Testing

### Running Tests
```bash
# Run all tests
composer test

# Run specific test suite
composer test -- --filter=Unit

# Run with coverage
composer test -- --coverage
```

### Writing Tests
- Write unit tests for new functionality
- Test edge cases and error conditions
- Maintain at least 80% code coverage
- Use descriptive test names

### Test Structure
```php
class Test_Event_Registration extends WP_UnitTestCase {
    
    public function test_register_user_for_event() {
        // Arrange
        $event_id = $this->create_test_event();
        $user_data = $this->get_test_user_data();
        
        // Act
        $result = DZ_Events_Registration_System::instance()->register_user_for_event($event_id, $user_data);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
    }
}
```

## 📚 Documentation

### Code Documentation
- Use PHPDoc comments for all functions and classes
- Include parameter types and return types
- Provide usage examples for complex functions

### User Documentation
- Update README.md for new features
- Add inline help text for admin interfaces
- Create video tutorials for complex features

## 🔒 Security

### Security Guidelines
- Never commit sensitive data (API keys, passwords)
- Sanitize all user inputs
- Use nonces for all forms and AJAX requests
- Follow WordPress security best practices

### Reporting Security Issues
- **DO NOT** create public issues for security vulnerabilities
- Email security issues to: security@designzeen.com
- Include detailed reproduction steps
- Allow time for response before public disclosure

## 🏷️ Versioning

### Semantic Versioning
We follow [Semantic Versioning](https://semver.org/):
- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Changelog
- Update CHANGELOG.md for all releases
- Include migration notes for breaking changes
- Document new features and improvements

## 🤝 Community Guidelines

### Code of Conduct
- Be respectful and inclusive
- Focus on constructive feedback
- Help others learn and grow
- Follow WordPress community guidelines

### Communication
- Use clear, descriptive commit messages
- Provide context in pull request descriptions
- Respond to feedback promptly
- Ask questions when unsure

## 📞 Getting Help

### Resources
- [WordPress Developer Documentation](https://developer.wordpress.org/)
- [Plugin API Reference](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)

### Contact
- GitHub Issues: For bugs and feature requests
- Email: support@designzeen.com
- Discord: [Join our community](https://discord.gg/zeen-events)

## 🎯 Roadmap

### Current Focus
- Performance optimization
- Security enhancements
- User experience improvements
- Enterprise features

### Future Plans
- Mobile app integration
- Advanced reporting
- Third-party integrations
- Multi-language support

---

Thank you for contributing to Zeen Events! Your contributions help make this plugin better for everyone. 🎉
