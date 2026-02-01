// Get DOM elements
const searchTypeSelect = document.getElementById('search_type');
const searchFields = document.getElementById('search_fields');
const searchResults = document.getElementById('search_results');
const loading = document.getElementById('loading');

// Check if we're on the search page
if (searchTypeSelect) {
    // When search type changes, show appropriate fields
    searchTypeSelect.addEventListener('change', function() {
        const searchType = this.value;
        searchFields.innerHTML = '';
        searchResults.innerHTML = '';
        
        if (!searchType) return;
        
        let fieldsHTML = '';
        
        if (searchType === 'employees') {
            fieldsHTML = `
                <div class="form-group">
                    <label>Search by Name, Email, Department, or Position</label>
                    <input type="text" id="search_input" placeholder="Start typing to search..." autocomplete="off">
                </div>
            `;
        } else if (searchType === 'attendance') {
            fieldsHTML = `
                <div class="form-group" id="employee_search_field">
                    <label>Search by Employee Name</label>
                    <input type="text" id="search_input" placeholder="Start typing employee name..." autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Month</label>
                    <input type="month" id="search_month">
                </div>
            `;
        } else if (searchType === 'leaves') {
            fieldsHTML = `
                <div class="form-group" id="employee_search_field">
                    <label>Search by Employee Name</label>
                    <input type="text" id="search_input" placeholder="Start typing employee name..." autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Leave Type</label>
                    <select id="leave_type">
                        <option value="">All Types</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Casual Leave">Casual Leave</option>
                        <option value="Annual Leave">Annual Leave</option>
                        <option value="Unpaid Leave">Unpaid Leave</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="leave_status">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
            `;
        }
        
        searchFields.innerHTML = fieldsHTML;
        
        // Hide employee search field if user is not admin (set by PHP)
        if (typeof isUserAdmin !== 'undefined' && !isUserAdmin) {
            const employeeField = document.getElementById('employee_search_field');
            if (employeeField) {
                employeeField.style.display = 'none';
            }
        }
        
        attachLiveSearchListeners();
    });
}

function attachLiveSearchListeners() {
    const searchInput = document.getElementById('search_input');
    const searchMonth = document.getElementById('search_month');
    const leaveType = document.getElementById('leave_type');
    const leaveStatus = document.getElementById('leave_status');
    
    // Live search on text input (with debounce)
    if (searchInput) {
        searchInput.addEventListener('input', debounce(performSearch, 500));
    }
    
    // Search on dropdown/date changes
    if (searchMonth) {
        searchMonth.addEventListener('change', performSearch);
    }
    if (leaveType) {
        leaveType.addEventListener('change', performSearch);
    }
    if (leaveStatus) {
        leaveStatus.addEventListener('change', performSearch);
    }
}


 //Debounce function to limit API calls
 
function debounce(func, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}


//Perform Ajax search and fetch results
function performSearch() {
    const searchType = searchTypeSelect.value;
    if (!searchType) return;
    
    // Get search parameters
    const searchInput = document.getElementById('search_input');
    const searchMonth = document.getElementById('search_month');
    const leaveType = document.getElementById('leave_type');
    const leaveStatus = document.getElementById('leave_status');
    
    const searchQuery = searchInput ? searchInput.value : '';
    const month = searchMonth ? searchMonth.value : '';
    const type = leaveType ? leaveType.value : '';
    const status = leaveStatus ? leaveStatus.value : '';
    
    // Build query string
    const params = new URLSearchParams({
        search_type: searchType,
        search_query: searchQuery,
        search_month: month,
        leave_type: type,
        leave_status: status
    });
    
    // Show loading indicator
    loading.style.display = 'block';
    searchResults.innerHTML = '';
    
    // Perform Ajax request
    fetch('search_ajax.php?' + params.toString())
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            loading.style.display = 'none';
            displayResults(data);
        })
        .catch(error => {
            loading.style.display = 'none';
            searchResults.innerHTML = '<div class="alert alert-error">Error performing search. Please try again.</div>';
            console.error('Search error:', error);
        });
}


 //Display search results in the UI
 
function displayResults(data) {
    if (data.error) {
        searchResults.innerHTML = `<div class="alert alert-error">${escapeHtml(data.error)}</div>`;
        return;
    }
    
    if (!data.results || data.results.length === 0) {
        searchResults.innerHTML = `
            <div class="card">
                <h3>Search Results (0 found)</h3>
                <p>No results found matching your search criteria.</p>
            </div>
        `;
        return;
    }
    
    let resultsHTML = `
        <div class="card">
            <h3>Search Results (${data.results.length} found)</h3>
    `;
    
    if (data.type === 'employees') {
        resultsHTML += buildEmployeesTable(data.results);
    } 
    else if (data.type === 'attendance') {
        resultsHTML += buildAttendanceTable(data.results, data.is_admin);
    }
    else if (data.type === 'leaves') {
        resultsHTML += buildLeavesTable(data.results, data.is_admin);
    }
    
    resultsHTML += '</div>';
    searchResults.innerHTML = resultsHTML;
}

/**
 * Build HTML table for employee results
 * @param {Array} employees - Array of employee objects
 */
function buildEmployeesTable(employees) {
    let html = `
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    employees.forEach(emp => {
        html += `
            <tr>
                <td>${escapeHtml(emp.employee_id)}</td>
                <td>${escapeHtml(emp.first_name + ' ' + emp.last_name)}</td>
                <td>${escapeHtml(emp.email)}</td>
                <td>${escapeHtml(emp.department)}</td>
                <td>${escapeHtml(emp.position)}</td>
                <td><span class="badge badge-info">${escapeHtml(emp.role)}</span></td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    return html;
}


// Build HTML table for attendance results
function buildAttendanceTable(records, isAdmin) {
    let html = `
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    ${isAdmin ? '<th>Employee</th>' : ''}
                    <th>Status</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    records.forEach(att => {
        const badgeClass = getStatusBadgeClass(att.status);
        
        html += `
            <tr>
                <td>${formatDate(att.date)}</td>
                ${isAdmin ? `<td>${escapeHtml(att.first_name + ' ' + att.last_name)}</td>` : ''}
                <td><span class="badge ${badgeClass}">${escapeHtml(att.status)}</span></td>
                <td>${att.check_in_time ? formatTime(att.check_in_time) : '-'}</td>
                <td>${att.check_out_time ? formatTime(att.check_out_time) : '-'}</td>
                <td>${escapeHtml(att.notes || '')}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    return html;
}
 //Build HTML table for leave request results
function buildLeavesTable(leaves, isAdmin) {
    let html = `
        <table>
            <thead>
                <tr>
                    ${isAdmin ? '<th>Employee</th>' : ''}
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    leaves.forEach(lr => {
        const badgeClass = getLeaveStatusBadgeClass(lr.status);
        
        html += `
            <tr>
                ${isAdmin ? `<td>${escapeHtml(lr.first_name + ' ' + lr.last_name)}</td>` : ''}
                <td>${escapeHtml(lr.leave_type)}</td>
                <td>${formatDate(lr.start_date)}</td>
                <td>${formatDate(lr.end_date)}</td>
                <td>${escapeHtml(lr.total_days)} days</td>
                <td>${escapeHtml(lr.reason)}</td>
                <td><span class="badge ${badgeClass}">${escapeHtml(lr.status)}</span></td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    return html;
}


 //Get badge class for attendance status

function getStatusBadgeClass(status) {
    switch(status) {
        case 'Present': return 'badge-success';
        case 'Absent': return 'badge-danger';
        case 'Late': return 'badge-warning';
        case 'Half-day': return 'badge-info';
        default: return 'badge-info';
    }
}

//Get badge class for leave status
 
function getLeaveStatusBadgeClass(status) {
    switch(status) {
        case 'Approved': return 'badge-success';
        case 'Rejected': return 'badge-danger';
        case 'Pending': return 'badge-warning';
        default: return 'badge-warning';
    }
}


 //Escape HTML to prevent XSS attacks
 
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}


 //Format date string to readable format
 
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}


 //Format time string to 12-hour format
 
function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
}