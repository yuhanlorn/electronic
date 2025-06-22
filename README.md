# Artwork Shop

## User Roles & Permissions

### Setup Information

This application uses Filament Shield for role-based access control. The following roles are available:

1. **Admin** - Has complete access to all features and functionality
2. **Artist** - Has limited access to manage their own products/artwork

### Default Users

The following users are created by default:

| Email | Password | Role |
|-------|----------|------|
| admin@admin.com | admin123 | Admin |

### Artist Users

The following artist users are available:

| Name | Email | Password | Role |
|------|-------|----------|------|
| CHAN DEN | chanden@artist.com | password | Artist |
| HEAN PHALLIN | heanphallin@artist.com | password | Artist |
| THY CHANTHON | thychanthon@artist.com | password | Artist |
| THACH MATU | thachmatu@artist.com | password | Artist |
| REAN SOPHEA | reansophea@artist.com | password | Artist |
| VAT CHANRA | vatchanra@artist.com | password | Artist |
| HANG RATHANA | hangrathana@artist.com | password | Artist |
| ROEUN SOPHEAK | roeunsopheak@artist.com | password | Artist |
| CHEA VOTHEA | cheavothea@artist.com | password | Artist |

### Artist Role Permissions

Artists have the following permissions:
- View, create, update, and delete their own products
- View any product (but can only manage their own)
- View categories
- View orders

### Running the Setup

To set up Filament Shield and user roles without refreshing the database:

```bash
php artisan db:seed --class=ShieldSeeder
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=ArtistSeeder
```

This will:
1. Set up all permissions and policies
2. Configure role assignments
3. Create the artist users

### Testing

Tests for artist permissions are available in `tests/Feature/ArtistUserTest.php`. Run them with:

```bash
php artisan test --filter=ArtistUserTest
``` 
