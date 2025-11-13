# Integrated Web Portal & CRM System

A comprehensive multi-portal web application designed for educational institutions to manage public information, student records, enquiry tracking, and administrative operations.

## 🌟 Features

### Public Website
- **Information Portal**: Comprehensive institutional information
- **Course Catalog**: Detailed course listings with filtering
- **Faculty Profiles**: Trainer and faculty information
- **Gallery & Media**: Photo and video showcases
- **Enquiry System**: Lead capture with document upload
- **SEO Optimized**: Search engine friendly structure

### Admin Portal
- **Complete System Control**: Full administrative access
- **User Management**: Create and manage all user accounts
- **Institution Oversight**: Manage multiple institutions
- **Enquiry Management**: Assign and track all enquiries
- **Student Oversight**: Access to all student records
- **Certificate Approval**: Centralized certificate management
- **Bulk Operations**: Mass data operations and exports
- **System Monitoring**: Performance and security monitoring

### Faculty Portal
- **Institution-Specific Access**: Manage only your institution's data
- **Student Registration**: Add new students with validation
- **Academic Records**: Upload and manage marklists
- **Certificate Requests**: Submit certificates for admin approval
- **Performance Analytics**: Institution-specific reports

### Enquirer Portal
- **Lead Management**: Access assigned enquiries
- **Call Tracking**: Log call outcomes and duration
- **Status Updates**: Update enquiry progress
- **Performance Metrics**: Call statistics and analytics

### Mobile API
- **Android Integration**: RESTful API for mobile apps
- **Secure Authentication**: JWT-based security
- **Real-time Sync**: Synchronize call data and status updates
- **Offline Capability**: Support for offline operations

## 🏗️ System Architecture

### Technology Stack
- **Backend**: PHP 7.4+ with MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Database**: MySQL 8.0+ / MariaDB 10.5+
- **Web Server**: Apache 2.4+ / Nginx 1.18+
- **API**: RESTful JSON API with JWT authentication
- **Security**: Multi-layer security with input validation

### Key Components
```
├── Public Website (/)
├── Admin Portal (/admin/)
├── Faculty Portal (/institution/)
├── Enquirer Portal (/enquiry/)
├── Mobile API (/api/)
├── Shared Backend (/includes/)
└── File Storage (/uploads/)
```

## 🚀 Quick Start

### Prerequisites
- PHP 7.4+ with required extensions
- MySQL 8.0+ or MariaDB 10.5+
- Apache 2.4+ or Nginx 1.18+
- 4GB+ RAM (recommended)
- 50GB+ storage (production)

### Installation
1. **Clone the repository**
   ```bash
   git clone https://github.com/your-org/integrated-crm.git
   cd integrated-crm
   ```

2. **Set up the database**
   ```bash
   mysql -u root -p < database/setup.sql
   ```

3. **Configure the application**
   ```bash
   cp includes/config.example.php includes/config.php
   # Edit config.php with your database credentials
   ```

4. **Set permissions**
   ```bash
   chmod -R 755 public/
   chmod -R 775 public/uploads/
   chmod -R 775 logs/
   ```

5. **Configure web server**
   - See [Installation Guide](docs/INSTALLATION_GUIDE.md) for detailed setup

### Default Login Credentials
- **Admin**: `admin` / `admin123`
- **Faculty**: `faculty` / `faculty123`
- **Enquirer**: `enquirer` / `enquirer123`

**⚠️ Change default passwords immediately after installation!**

## 📚 Documentation

### User Guides
- [**Installation Guide**](docs/INSTALLATION_GUIDE.md) - Complete setup instructions
- [**Admin User Manual**](docs/ADMIN_USER_MANUAL.md) - Administrator guide
- [**Faculty User Manual**](docs/FACULTY_USER_MANUAL.md) - Faculty portal guide
- [**API Documentation**](docs/API_DOCUMENTATION.md) - Mobile API reference
- [**Maintenance Guide**](docs/MAINTENANCE_GUIDE.md) - System maintenance

### Technical Documentation
- [**System Requirements**](docs/INSTALLATION_GUIDE.md#system-requirements)
- [**Security Configuration**](docs/INSTALLATION_GUIDE.md#security-configuration)
- [**Performance Optimization**](docs/MAINTENANCE_GUIDE.md#performance-optimization)
- [**Troubleshooting**](docs/MAINTENANCE_GUIDE.md#troubleshooting-guide)

## 🔧 Configuration

### Database Configuration
```php
// includes/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'integrated_crm');
define('DB_USER', 'crm_user');
define('DB_PASS', 'your_secure_password');
```

### Security Settings
```php
// Security configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes
```

### File Upload Settings
```php
// File upload configuration
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
```

## 🔒 Security Features

### Authentication & Authorization
- **Multi-role Authentication**: Admin, Faculty, Enquirer roles
- **Session Management**: Secure session handling with timeout
- **Password Security**: Bcrypt hashing with salt
- **Brute Force Protection**: Account lockout after failed attempts

### Data Protection
- **Input Validation**: Comprehensive input sanitization
- **SQL Injection Prevention**: Parameterized queries
- **XSS Protection**: Output encoding and CSP headers
- **CSRF Protection**: Token-based CSRF prevention
- **File Upload Security**: Type validation and malware scanning

### System Security
- **HTTPS Enforcement**: SSL/TLS encryption
- **Security Headers**: Comprehensive security headers
- **Access Control**: Directory-level access restrictions
- **Audit Logging**: Complete activity logging

## 📊 Features Overview

### Advanced Filtering System
- **Multi-criteria Filtering**: Complex filter combinations
- **Real-time Search**: Instant search results
- **Saved Filters**: Reusable filter configurations
- **Export Filtered Data**: Download filtered results

### Bulk Operations
- **Mass Data Operations**: Bulk create, update, delete
- **Batch Processing**: Handle large datasets efficiently
- **Progress Tracking**: Monitor operation progress
- **Rollback Capability**: Undo bulk operations

### Reporting & Analytics
- **Comprehensive Reports**: Multi-format report generation
- **Performance Analytics**: System and user metrics
- **Custom Dashboards**: Role-specific dashboards
- **Scheduled Reports**: Automated report delivery

### Mobile Integration
- **Native Android Support**: Dedicated mobile API
- **Offline Synchronization**: Work offline, sync later
- **Real-time Updates**: Live data synchronization
- **Push Notifications**: Important alerts and updates

## 🧪 Testing

### Running Tests
```bash
# Run integration tests
php tests/SimpleIntegrationTest.php

# Run security tests
php tests/SecurityTester.php

# Run performance tests
php tests/PerformanceTester.php

# Run comprehensive test suite
php tests/SecurityPerformanceTestRunner.php
```

### Test Coverage
- **Integration Testing**: Complete workflow validation
- **Security Testing**: Vulnerability assessment
- **Performance Testing**: Load and stress testing
- **API Testing**: Mobile API endpoint validation

## 🔧 Maintenance

### Regular Tasks
- **Daily**: Health checks, log review, backup verification
- **Weekly**: Database optimization, log cleanup, security updates
- **Monthly**: Performance analysis, user audit, data archival

### Monitoring
- **System Health**: Automated health checks
- **Performance Metrics**: Response time and resource monitoring
- **Security Alerts**: Failed login and intrusion detection
- **Error Tracking**: Comprehensive error logging

### Backup Strategy
- **Database Backups**: Automated daily backups with compression
- **File Backups**: Regular file system backups
- **Recovery Procedures**: Documented recovery processes
- **Disaster Recovery**: Complete disaster recovery plan

## 🤝 Contributing

### Development Guidelines
1. Follow PSR coding standards
2. Write comprehensive tests
3. Document all changes
4. Use semantic versioning
5. Submit pull requests for review

### Code Structure
```
├── public/           # Web-accessible files
├── includes/         # Backend logic and utilities
├── database/         # Database schemas and migrations
├── tests/           # Test suites and fixtures
├── docs/            # Documentation
└── logs/            # Application logs
```

## 📈 Performance

### Optimization Features
- **Database Indexing**: Optimized database queries
- **Caching System**: Multi-layer caching strategy
- **File Compression**: Automated file compression
- **CDN Ready**: Content delivery network support

### Scalability
- **Horizontal Scaling**: Multi-server deployment support
- **Load Balancing**: Load balancer compatibility
- **Database Clustering**: MySQL cluster support
- **Microservices Ready**: API-first architecture

## 🐛 Troubleshooting

### Common Issues
- **Database Connection**: Check credentials and service status
- **File Permissions**: Verify upload directory permissions
- **Performance Issues**: Review database indexes and queries
- **Session Problems**: Check session configuration and storage

### Getting Help
- Check the [Troubleshooting Guide](docs/MAINTENANCE_GUIDE.md#troubleshooting-guide)
- Review system logs for error messages
- Run the integration test suite
- Contact support with specific error details

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Built with modern web technologies
- Follows security best practices
- Designed for educational institutions
- Optimized for performance and scalability

## 📞 Support

### Contact Information
- **Technical Support**: support@your-domain.com
- **Documentation**: docs@your-domain.com
- **Security Issues**: security@your-domain.com
- **General Inquiries**: info@your-domain.com

### Community
- **GitHub Issues**: Report bugs and feature requests
- **Documentation**: Contribute to documentation
- **Discussions**: Join community discussions
- **Wiki**: Access community wiki

---

## 🚀 Getting Started Checklist

- [ ] Review system requirements
- [ ] Follow installation guide
- [ ] Configure database and web server
- [ ] Change default passwords
- [ ] Set up SSL certificate
- [ ] Configure backups
- [ ] Run integration tests
- [ ] Review security settings
- [ ] Set up monitoring
- [ ] Train users on system

**Ready to deploy? Follow our [Installation Guide](docs/INSTALLATION_GUIDE.md) for detailed setup instructions.**

---

*Built with ❤️ for educational institutions worldwide*