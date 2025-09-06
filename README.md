# MetaSoft Letterhead Generator

Professional business letterhead generation system built with Laravel. Create stunning letterheads in PDF and Word formats with customizable templates, logo integration, and rich text editing.

## 🚀 Features

### Multi-Format Output
- **PDF Generation**: High-quality PDF documents with print optimization
- **Word Documents**: Editable .docx files with professional formatting
- **Template System**: Four distinct professional designs
- **Custom Branding**: Logo integration with intelligent fallbacks

### Template Designs
- **Classic Business**: Traditional formal letterhead with elegant typography
- **Modern Green**: Contemporary design with environmental accent colors
- **Corporate Blue**: Professional blue-themed layout for corporate identity
- **Elegant Gray**: Sophisticated grayscale design for refined branding

### Advanced Features
- **Paper Size Flexibility**: US Letter, A4, Legal, and custom dimensions
- **Rich Text Editor**: Summernote WYSIWYG editor for letter content
- **Logo Processing**: Automatic resizing and format optimization
- **Responsive Design**: Mobile-friendly form interface
- **Real-time Validation**: Client-side and server-side form validation

## 🛠️ Technology Stack

- **Framework**: Laravel 10.x
- **PDF Generation**: DomPDF with Barryvdh Laravel wrapper
- **Word Processing**: PhpWord library for DOCX generation
- **Frontend**: Bootstrap 5, jQuery, Summernote editor
- **Authentication**: Laravel Breeze
- **File Processing**: Laravel file storage and validation

## 📋 Requirements

- PHP 8.1+
- Composer
- Node.js 16+
- NPM or Yarn
- MySQL/MariaDB or SQLite

## ⚡ Quick Start

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/metasoftdevs/letterhead-generator.git
   cd letterhead-generator
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database configuration**
   ```bash
   # Configure your database in .env file
   php artisan migrate
   ```

6. **Build assets**
   ```bash
   npm run dev
   ```

7. **Start the application**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to access the application.

## 📖 Usage

### Creating a Letterhead

1. **Access the Generator**
   - Navigate to `/letterhead` after authentication
   - Select from four professional templates

2. **Configure Settings**
   - Choose paper size (Letter, A4, Legal, or Custom)
   - Select output format (PDF or Word)
   - Upload company logo (optional)

3. **Enter Information**
   - Company name and address (required)
   - Contact information (phone, email, website)
   - Recipient details (optional)
   - Letter content with rich text formatting

4. **Generate Document**
   - Click "Generate & Download"
   - Document will be created and downloaded automatically

### Template Customization

Each template includes:
- **Color Schemes**: Brand-consistent color palettes
- **Typography**: Professional font selections
- **Layout Options**: Header/content/footer organization
- **Responsive Elements**: Dynamic sizing based on paper dimensions

## 🏗️ Architecture

### Service Layer
- **LetterheadTemplateService**: Word document generation with PhpWord
- **PdfLetterheadService**: PDF generation with DomPDF and HTML/CSS

### Controller Layer
- **LetterheadController**: Request handling, validation, and service orchestration

### Security Features
- **File Upload Security**: MIME type validation and size limits
- **Input Sanitization**: XSS prevention and HTML filtering
- **Authentication**: Route protection with Laravel middleware
- **CSRF Protection**: Token validation on all forms

## 🔧 Configuration

### Template Settings
Templates can be customized in the service classes:
- Color schemes and branding
- Typography and font selections
- Margin and spacing configurations
- Logo positioning and sizing

### File Storage
Configure upload directories in `config/filesystems.php`:
```php
'temp' => [
    'driver' => 'local',
    'root' => storage_path('app/public/temp'),
],
```

### PDF Settings
Customize PDF generation in `PdfLetterheadService`:
- Paper sizes and orientations
- Font settings and DPI
- Print optimizations

## 🧪 Testing

Run the test suite:
```bash
# Unit tests
php artisan test

# Feature tests with coverage
php artisan test --coverage
```

### Test Coverage
- Service layer functionality
- Controller request handling
- File upload processing
- Template generation accuracy

## 📚 API Documentation

### Available Templates
- `classic`: Traditional business format
- `modern_green`: Contemporary green design
- `corporate_blue`: Professional blue theme
- `elegant_gray`: Sophisticated grayscale

### Paper Sizes
- `us_letter`: 8.5" × 11"
- `a4`: 210mm × 297mm  
- `legal`: 8.5" × 14"
- `custom`: User-defined dimensions

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Add tests for new functionality
- Update documentation for API changes
- Use conventional commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- **Metasoft Developers** - *Initial work* - [Metasoftdevs](https://www.metasoftdevs.com)

## 📞 Support

- **Email**: info@metasoftdevs.com
- **Website**: https://www.metasoftdevs.com
- **Issues**: [GitHub Issues](https://github.com/metasoftdevs/letterhead-generator/issues)

## 🔄 Changelog

### Version 1.0.0
- Initial release with four template designs
- PDF and Word document generation
- Rich text editing capabilities
- Logo integration and processing
- Responsive form interface
- Authentication and security features

---

Made with ❤️ by [Metasoft Developers](https://www.metasoftdevs.com)