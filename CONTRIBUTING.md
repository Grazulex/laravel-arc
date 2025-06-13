# Contributing to Laravel Arc

Thank you for considering contributing to Laravel Arc! Here are some guidelines to help you get started.

## Development Setup

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR_USERNAME/laravel-arc.git`
3. Install dependencies: `composer install`
4. Create a branch: `git checkout -b feature/your-feature-name`

## Running Tests

We use [Pest](https://pestphp.com/) for testing:

```bash
# Run all tests
composer test

# Run specific test suites
composer test-unit
composer test-feature

# Run with coverage
composer test-coverage
```

## Coding Standards

- Follow PSR-12 coding standards
- Add tests for any new functionality
- Update documentation when needed
- Use meaningful commit messages

## Pull Request Process

1. Ensure all tests pass
2. Update the README.md if needed
3. Update the CHANGELOG.md following [Keep a Changelog](https://keepachangelog.com/) format
4. Submit your pull request with a clear description

## Commit Message Format

We follow [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` New features
- `fix:` Bug fixes
- `docs:` Documentation changes
- `test:` Adding or updating tests
- `refactor:` Code refactoring
- `style:` Code style changes
- `chore:` Maintenance tasks

Example: `feat: add support for nested DTO validation`

## Questions?

Feel free to open an issue for any questions or suggestions!

