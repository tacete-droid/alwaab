# Al Waab Building Materials - Excel Upload System Guide

## Overview

This system allows you to upload an Excel file containing your product catalog, and automatically generate a mini-store where customers can browse products, select sizes, add quantities, and submit quote requests.

## How to Use

### Step 1: Prepare Your Excel File

Your Excel file should have the following columns (in this order):

| Column Name | Description | Required | Example |
|-------------|-------------|----------|---------|
| Product ID | Unique identifier for the product | ✅ Yes | P001 |
| Product Name | Name of the product | ✅ Yes | CPVC Pipe 20mm |
| Category | Product category | No | Pipes |
| Description | Product description | No | High-pressure CPVC pipe for hot and cold water |
| Sizes | Available sizes (comma or semicolon separated) | No | 16mm, 20mm, 25mm, 32mm |
| Image | Image URL/path | No | images/pipe.jpg |
| Unit | Unit of measurement | No | pcs |
| Notes | Additional notes | No | PN16 rated |

### Step 2: Upload the Excel File

1. Navigate to the **Upload Excel** page (click the "Upload Excel" button in the header)
2. Drag and drop your Excel file or click to browse
3. Wait for the file to be processed
4. Preview your products in the catalog preview section

### Step 3: Add Products to Quote

1. Browse the uploaded products
2. Click "Add to Quote" on products you want to include
3. Products will be added to your quote cart

### Step 4: Submit Quote Request

1. Click the quote cart button (bottom right corner)
2. Select sizes and quantities for each product
3. Click "Request Quote"
4. Fill in your contact details
5. Submit the form
6. You will receive a confirmation message

## Supported File Formats

- `.xlsx` (Excel 2007+)
- `.xls` (Excel 97-2003)
- `.csv` (Comma-separated values)

## Sample Template

You can download a sample template from the upload page, or create your own with this structure:

```
Product ID | Product Name | Category | Description | Sizes | Image | Unit | Notes
P001 | CPVC Pipe | Pipes | High-pressure CPVC pipe | 16mm; 20mm; 25mm | images/pipe.jpg | pcs | PN16
F001 | Elbow 90° | Fittings | 90 degree elbow | 16mm; 20mm; 25mm | images/elbow.jpg | pcs | 
V001 | Ball Valve | Valves | Professional ball valve | 16mm; 20mm; 25mm | images/valve.jpg | pcs | Solvent cement
```

## Important Notes

1. **Product ID** and **Product Name** are required fields
2. Products are stored in the browser's localStorage
3. Clear browser data will remove uploaded products
4. For best results, use relative image paths (e.g., `images/product.jpg`)
5. The system supports unlimited products in a single upload

## Technical Details

- Uses SheetJS (xlsx) library for Excel parsing
- Products are stored in localStorage under key `excelProducts`
- Quote cart is stored in localStorage under key `quoteCart`
- Compatible with all modern browsers

## Troubleshooting

### Products not showing up?
- Check that your Excel file has the required columns
- Ensure Product ID and Product Name are filled for each row
- Try re-uploading the file

### Images not loading?
- Verify the image paths are correct
- Use relative paths (e.g., `images/photo.jpg`)
- Ensure images exist in the specified location

### Quote request not sending?
- Check that you have selected at least one product
- Ensure quantities are specified for selected sizes
- Verify your email client is configured correctly

## Contact Support

For technical support, contact:
- Phone: +971 4 251 4228
- Mobile: +971 54 769 6440
- Email: info@alwaab.ae