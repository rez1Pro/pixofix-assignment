# PixoFix - File Management System

## File Management and Organization

The system implements a comprehensive workflow for managing and organizing file uploads, especially for large batch operations:

### File Naming and Organization

Files are properly named and organized using the following pattern:

- Original folder/directory structure from uploads is preserved
- Filenames follow the format: `[prefix_]originalname_order{ID}_{timestamp}_{random}.extension`
- Completed files include status indicators in the filename
- Files are automatically sorted into their original folders after processing

### Batch Processing Workflow

1. **Order Creation**: Admin creates an order with multiple image files (100+) organized in folders
2. **File Claiming**: Employees claim batches of 10-20 files at a time to work on
3. **Isolation**: Claimed files become inaccessible to other employees to prevent duplication
4. **Processing**: Employees edit files using integrated or external editors
5. **Completion**: Employees mark files as completed, maintaining original folder structure
6. **Admin Approval**: Admin reviews and approves the completed order

### Key Features

- Batch claiming with configurable batch sizes
- Proper file organization that preserves original folder structure
- Consistent file naming convention for tracking and organization
- Real-time progress tracking
- Support for large orders with hundreds of files
- Automatic directory creation and maintenance

### Technical Implementation

- Service-based architecture for separation of concerns
- Transactional processing to ensure data consistency
- Race-condition prevention using database locks
- Directory-based filtering and organization
- Automatic metadata extraction and preservation

## Development Setup

[Add development setup instructions here]
