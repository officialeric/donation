# Orphanage Functionality Testing Guide

## Overview
This guide helps you test all the orphanage management functionality that has been implemented.

## Prerequisites
1. Ensure the database updates have been applied:
   ```sql
   ALTER TABLE `orphanages` MODIFY `id` int NOT NULL AUTO_INCREMENT;
   ```
2. Ensure the uploads directory exists with proper permissions
3. Admin user should be logged in

## Test Cases

### 1. Add Orphanage (Standalone Page)

**Test Steps:**
1. Navigate to Admin Dashboard → Orphanages
2. Click "Add Orphanage (Page)" button
3. Fill out the form with test data:
   - **Name**: "Test Orphanage 1"
   - **Location**: "Test City, Test State"
   - **Description**: "This is a test orphanage for functionality testing..."
   - **Contact Person**: "John Doe"
   - **Contact Phone**: "+1234567890"
   - **Contact Email**: "test@orphanage.com"
   - **Bank Account**: "Test Bank, Account: 123456789"
   - **Status**: "Active"
   - **Image**: Upload a test image (optional)
4. Check the terms checkbox
5. Click "Create Orphanage"

**Expected Results:**
- Form validates all required fields
- Email validation works
- Phone number formatting works
- Image upload works (if provided)
- Success message appears
- Redirected to orphanages list
- New orphanage appears in the list

### 2. Add Orphanage (Modal)

**Test Steps:**
1. Navigate to Admin Dashboard → Orphanages
2. Click "Add Orphanage (Modal)" button
3. Fill out the modal form with different test data:
   - **Name**: "Test Orphanage 2"
   - **Location**: "Another City, Another State"
   - **Description**: "Second test orphanage..."
   - **Contact Person**: "Jane Smith"
   - **Contact Phone**: "+0987654321"
   - **Contact Email**: "jane@orphanage2.com"
   - **Bank Account**: "Another Bank, Account: 987654321"
   - **Status**: "Active"
4. Click "Save"

**Expected Results:**
- Modal form works correctly
- Validation works
- Success message appears
- Modal closes
- New orphanage appears in the list

### 3. View Orphanage Details

**Test Steps:**
1. In the orphanages list, click "View" button for any orphanage
2. Review the displayed information

**Expected Results:**
- All orphanage details are displayed correctly
- Statistics show (total donations, donors, campaigns)
- Associated campaigns are listed (if any)
- Quick action buttons work
- "View Public Page" opens the public orphanage details page

### 4. Edit Orphanage

**Test Steps:**
1. In the orphanages list, click "Edit" button for any orphanage
2. Modify some fields:
   - Change the description
   - Update contact information
   - Change status to "Inactive"
   - Upload a new image (optional)
3. Click "Update Orphanage"

**Expected Results:**
- Form is pre-populated with current data
- Changes are saved successfully
- Success message appears
- Updated information appears in the orphanages list
- Image is updated (if uploaded)

### 5. Delete Orphanage

**Test Steps:**
1. Try to delete an orphanage that has donations
2. Try to delete an orphanage that has campaigns
3. Try to delete an orphanage with no donations or campaigns

**Expected Results:**
- Cannot delete orphanages with donations (error message)
- Cannot delete orphanages with campaigns (error message)
- Can delete orphanages with no dependencies
- Confirmation modal appears
- Success message after deletion

### 6. Orphanage Campaign Management

**Test Steps:**
1. In the orphanages list, click "Campaigns" button for any orphanage
2. Verify the filtered campaign view
3. Try adding a campaign from this view

**Expected Results:**
- Only campaigns for the selected orphanage are shown
- "Back to Orphanages" button works
- Add campaign form pre-selects the orphanage
- Breadcrumb navigation works correctly

### 7. Form Validation Testing

**Test Steps:**
1. Try submitting forms with missing required fields
2. Try submitting with invalid email addresses
3. Try uploading invalid file types
4. Try uploading files larger than 5MB
5. Try creating orphanages with duplicate names

**Expected Results:**
- Required field validation works
- Email format validation works
- File type validation works
- File size validation works
- Duplicate name validation works
- Clear error messages are displayed

### 8. Image Upload Testing

**Test Steps:**
1. Upload different image formats (JPG, PNG, GIF)
2. Try uploading non-image files
3. Try uploading very large images
4. Update an orphanage image
5. View the uploaded images

**Expected Results:**
- Valid image formats are accepted
- Invalid formats are rejected
- Large files are rejected
- Images are properly stored in uploads/orphanages/
- Images display correctly in forms and public pages
- Old images are deleted when updating

### 9. Status Management

**Test Steps:**
1. Create orphanages with different statuses
2. Change orphanage status from active to inactive
3. Check public visibility

**Expected Results:**
- Status field works in both add and edit forms
- Inactive orphanages are not visible to public users
- Status changes are saved correctly
- Status is displayed correctly in lists

### 10. Navigation and User Experience

**Test Steps:**
1. Test all navigation links and buttons
2. Test breadcrumb navigation
3. Test back buttons
4. Test quick action buttons

**Expected Results:**
- All navigation works correctly
- Breadcrumbs show correct path
- Back buttons return to correct pages
- Quick actions work as expected

## Error Scenarios to Test

### Database Errors
- Test with database connection issues
- Test with invalid SQL queries

### File System Errors
- Test with read-only uploads directory
- Test with insufficient disk space

### Security Testing
- Test direct access to upload files
- Test directory browsing prevention
- Test file execution prevention

## Performance Testing
- Test with large number of orphanages
- Test with large image uploads
- Test form submission speed

## Browser Compatibility
Test on:
- Chrome
- Firefox
- Safari
- Edge
- Mobile browsers

## Cleanup After Testing
1. Delete test orphanages
2. Remove test images from uploads directory
3. Reset any modified data

## Common Issues and Solutions

### Issue: Images not displaying
**Solution**: Check file permissions on uploads directory

### Issue: Form validation not working
**Solution**: Check JavaScript console for errors

### Issue: Database errors
**Solution**: Verify database schema and AUTO_INCREMENT setting

### Issue: File upload fails
**Solution**: Check PHP upload settings (upload_max_filesize, post_max_size)

## Success Criteria
✅ All forms work correctly
✅ Validation works as expected
✅ Image uploads work properly
✅ Navigation is smooth and intuitive
✅ Error handling is user-friendly
✅ Data is saved and retrieved correctly
✅ Security measures are in place
✅ Performance is acceptable

## Notes
- Test with both valid and invalid data
- Pay attention to error messages and user feedback
- Verify that all buttons and links work
- Check that data persists correctly
- Ensure proper security measures are in place
