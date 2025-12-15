# Deploying EtudeSync to Render

## Quick Setup

This project is configured to deploy to Render using the `render.yaml` blueprint.

### Option 1: Using render.yaml (Recommended)

1. Push your code to GitHub
2. Go to [Render Dashboard](https://dashboard.render.com/)
3. Click "New" → "Blueprint"
4. Connect your GitHub repository
5. Render will automatically detect `render.yaml` and create:
   - A web service running your PHP application
   - A MySQL database
   - All necessary environment variables

### Option 2: Manual Setup

#### Step 1: Create a MySQL Database
1. In Render Dashboard, click "New" → "PostgreSQL" or "MySQL"
   - **Note**: Render's free tier only supports PostgreSQL, not MySQL
   - You may need to convert your MySQL schema to PostgreSQL or use an external MySQL provider

2. For MySQL, you can use:
   - [PlanetScale](https://planetscale.com/) (Free tier available)
   - [Railway](https://railway.app/) (MySQL support)
   - Or upgrade to Render paid plan

#### Step 2: Create the Web Service
1. Click "New" → "Web Service"
2. Connect your GitHub repository
3. Configure:
   - **Name**: etudesync
   - **Environment**: Node
   - **Build Command**: `echo "No build required"`
   - **Start Command**: `npm start`
   - **Plan**: Free

#### Step 3: Set Environment Variables
Add these environment variables in the Render dashboard:
- `DB_HOST`: Your database host
- `DB_NAME`: etudesync
- `DB_USER`: Your database username
- `DB_PASS`: Your database password

#### Step 4: Import Database Schema
After the database is created:
1. Connect to your database using the connection string from Render
2. Run the SQL schema: `sql/etudesync_schema.sql`

## Important Notes

### Render Free Tier Limitations
- **No MySQL on free tier**: Use PostgreSQL or external MySQL
- **Web service sleeps after 15 min of inactivity**: First request after sleep takes ~30 seconds
- **750 hours/month**: Enough for 1 service running 24/7

### For PostgreSQL Migration
If using Render's PostgreSQL, you'll need to convert your schema:
- Change `AUTO_INCREMENT` to `SERIAL` or `BIGSERIAL`
- Review MySQL-specific syntax
- Update connection DSN in `includes/db.php` to use `pgsql:` instead of `mysql:`

### Alternative: Use External MySQL
Recommended free MySQL hosting:
1. **PlanetScale** - Serverless MySQL, 5GB free
2. **Railway** - $5 credit/month, supports MySQL
3. **Clever Cloud** - Free tier with MySQL

## Troubleshooting

### Error: "bash: line 1: start: command not found"
✅ Fixed! This was caused by missing `package.json`. Now included.

### Database Connection Failed
- Verify environment variables are set correctly
- Check database is running and accessible
- Confirm database credentials

### Files Not Found (404 errors)
- Ensure the start command uses `-t public` to serve from the public directory
- Current command: `php -S 0.0.0.0:${PORT:-8080} -t public`

## Need Help?
- [Render Documentation](https://render.com/docs)
- [Render Community](https://community.render.com/)
