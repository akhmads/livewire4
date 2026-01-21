# Queue Management Module

Modul ini menyediakan interface untuk monitoring dan manajemen Laravel Queue dengan fitur lengkap.

## 📁 File Structure

### Models

- `app/Models/Job.php` - Model untuk jobs table dengan accessor dan scopes
- `app/Models/FailedJob.php` - Model untuk failed_jobs table dengan retry/delete methods

### Livewire Components

- `app/Livewire/Queue/JobQueue.php` - Component untuk menampilkan active jobs
- `app/Livewire/Queue/FailedJobs.php` - Component untuk menampilkan failed jobs

### Views

- `resources/views/livewire/queue/job-queue.blade.php` - UI untuk active jobs
- `resources/views/livewire/queue/failed-jobs.blade.php` - UI untuk failed jobs

## 🚀 Features

### Active Jobs Monitor

✅ **Real-time Statistics**

- Total jobs in queue
- Pending jobs count
- Processing jobs count
- Delayed jobs count

✅ **Advanced Filtering**

- Search by job name or queue
- Filter by queue (default, high, low, etc)
- Filter by status (pending, processing, delayed)
- Pagination support

✅ **Job Information**

- Job ID and name
- Queue name
- Status badge (color-coded)
- Attempts count
- Created and Available timestamps
- Human-readable time differences

✅ **Actions**

- Delete individual jobs
- Auto-refresh
- Real-time updates

### Failed Jobs Management

✅ **Comprehensive View**

- Total failed jobs counter
- Job name and UUID
- Queue information
- Exception preview
- Failed timestamp with human-readable format

✅ **Powerful Actions**

- **Retry** individual failed jobs
- **Delete** individual failed jobs
- **Retry All** - restore all failed jobs to queue
- **Flush All** - delete all failed jobs
- **View Full Exception** - modal with complete stack trace

✅ **Advanced Features**

- Search across job name, queue, and exception
- Filter by queue
- Exception modal with syntax highlighting
- Toast notifications for actions
- Confirmation dialogs for destructive actions

## 🔧 Technical Highlights

### Performance Optimizations

1. **Query Optimization**
    - Efficient database queries with proper indexing
    - Pagination to handle large datasets
    - Lazy loading with debounced search (300ms)

2. **Smart Caching**
    - Computed properties for stats
    - Session-based filter persistence
    - Query string state management

3. **Livewire Best Practices**
    - Wire:model.live for reactive filters
    - Wire:key for proper list rendering
    - Event dispatching for notifications
    - Modular component design

### Code Quality

1. **Type Safety**
    - PHP 8.1+ features (attributes, enums)
    - Strict typing in models and components
    - Proper docblocks

2. **Security**
    - Wire:confirm for destructive actions
    - CSRF protection
    - Middleware authentication
    - Input validation

3. **Clean Code**
    - Single Responsibility Principle
    - DRY (Don't Repeat Yourself)
    - Readable method names
    - Proper separation of concerns

## 📋 Usage

### Access the Module

1. **Active Jobs**: Navigate to `/queue/jobs` or use menu "Queue Monitor > Active Jobs"
2. **Failed Jobs**: Navigate to `/queue/failed` or use menu "Queue Monitor > Failed Jobs"

### Common Operations

**Monitor Queue**

```
1. Check real-time statistics in cards
2. Use filters to find specific jobs
3. View job details and status
4. Delete stuck jobs if needed
```

**Handle Failed Jobs**

```
1. Review failed jobs list
2. Click "View Full Exception" to see error details
3. Retry individual jobs or all at once
4. Flush jobs that can't be recovered
```

## 🎨 UI Features

- **Responsive Design** - Works on mobile, tablet, and desktop
- **Dark Mode Support** - Adapts to theme preference
- **Color-coded Status** - Visual indicators for job states
- **Toast Notifications** - User feedback for all actions
- **Empty States** - Clear messaging when no data
- **Loading States** - Spinner indicators during operations

## ⚡ Performance Tips

1. **For High Volume Queues**
    - Use queue filtering to focus on specific queues
    - Adjust perPage for faster loading
    - Use search to find specific jobs

2. **Regular Maintenance**
    - Review failed jobs regularly
    - Retry or flush old failed jobs
    - Monitor queue processing speed

## 🔐 Security Notes

- All routes protected by `auth` middleware
- Confirmation required for destructive actions
- Input sanitization on all searches
- Proper CSRF token validation

## 🎯 Best Practices

1. **Queue Management**
    - Set up proper queue workers
    - Configure retry logic in jobs
    - Use delays for rate-limited APIs
    - Implement proper error handling

2. **Monitoring**
    - Check failed jobs daily
    - Review exception patterns
    - Monitor queue processing times
    - Set up alerts for high failure rates

3. **Maintenance**
    - Clear old jobs periodically
    - Optimize long-running jobs
    - Use job batching when appropriate
    - Implement idempotent jobs

---

**Created**: January 2026
**Laravel Version**: 11.x
**Livewire Version**: 4.x
**Author**: GitHub Copilot
