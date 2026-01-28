<!---Page view styles-->
<style>
.issue-tracker {
    background: #f8fafc;
    min-height: 100vh;
    padding: 20px;
}

.tracker-header {
    background: white;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
}

.header-actions {
    align-items: center;
    gap: 10px;
}

#loadAllBtn {
    font-size: 12px;
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

#loadAllBtn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#dataInfo {
    white-space: nowrap;
}

.tracker-title {
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.stat-title {
    font-size: 14px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    line-height: 1;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    } 
}
</style>

<!---Accordian styles-->
<style>
	.accordion-card {
		border: 1px solid #dee2e6;
		border-radius: 12px;
		margin-bottom: 8px;
		background: #fff;
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
		transition: all 0.3s ease;
		overflow: hidden;
	}
	
	.accordion-card:hover {
		box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
		transform: translateY(-2px);
	}
	
	.accordion-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 8px 15px;
		cursor: pointer;
		font-weight: 600;
		font-size: 1.1rem;
		color: #000;
		background: #f7f7f8;
		user-select: none;
		position: relative;
		transition: all 0.3s ease;
	}
	
	.accordion-header:hover {
		opacity: 0.9;
	}
	
	.accordion-header::after {
		content: '\f078';
		font-family: 'Font Awesome 6 Free';
		font-weight: 900;
		font-size: 0.9rem;
		transition: transform 0.3s ease;
	}
	
	.accordion-header.active::after {
		transform: rotate(180deg);
	}
	
	.status-info {
		display: flex;
		align-items: center;
		gap: 12px;
		font-size: 1.3rem;
	}
	
	.status-icon {
		width: 30px;
		height: 30px;
		border-radius: 50%;
		background: rgba(255, 255, 255, 0.2);
		display: flex;
		align-items: center;
		justify-content: center;
		font-weight: bold;
		font-size: 1.1rem;
	}
	
	.item-count {
		background: #e8e8e9;
		color: black;
		padding: 6px 12px;
		border-radius: 20px;
		font-size: 1.5rem;
		font-weight: 500;
		text-align: center;
	}
	
	.accordion-body {
		max-height: 0;
		overflow: hidden;
		transition: max-height 0.4s ease-out, padding 0.4s ease-out;
		background-color: #ffffff;
	}
	
	.accordion-body.active {
		max-height: 100%;
		padding: 24px;
	}
	
	.task-item {
		display: flex;
		align-items: center;
		padding: 5px 10px;
		border-bottom: 1px solid #f0f0f1;
		background: #f7f7f8;
		transition: all 0.2s ease;
		border-radius: 8px;
		gap: 10px;
	}
	
	.task-item:last-child {
		border-bottom: none;
	}
	
	.task-item:hover {
		background-color: #e8e8e9;
		margin: 0 -12px;
		padding-left: 12px;
		padding-right: 12px;
		border-radius: 8px;
	}
	
	.task-id {
		width: 80px;
		flex-shrink: 0;
		font-weight: 600;
		color: #1976d2;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 1.5rem;
	}
	
	.task-title {
		width: 200px;
		flex-shrink: 0;
		font-weight: 600;
		color: #2d3748;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 1.5rem;
	}
	
	.task-labels {
		width: 120px;
		flex-shrink: 0;
		padding: 5px 10px;
		border: 1px solid #cccccc;
		background: #f2f2f4;
		color: #000;
		border-radius: 20px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 1.5rem;
	}
	
	.task-component {
		width: 100px;
		flex-shrink: 0;
		padding: 5px 10px;
		border: 1px solid #cccccc;
		background: #f2f2f4;
		color: #000;
		border-radius: 20px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 1.5rem;
	}
	
	.task-milestone {
		width: 100px;
		flex-shrink: 0;
		padding: 5px 10px;
		border: 1px solid #cccccc;
		background: #f2f2f4;
		color: #000;
		border-radius: 20px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 1.5rem;
	}
	
	.task-type {
		width: 100px;
		flex-shrink: 0;
		padding: 5px 10px;
		border: 1px solid #cccccc;
		background: #f2f2f4;
		color: #000;
		border-radius: 20px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 1.5rem;
	}
	
	.user-avatar-container {
		width: 40px;
		flex-shrink: 0;
		margin-left: auto;
	}
	
	.task-parent {
		margin-bottom: 8px;
		padding: 4px 8px;
		background: #e3f2fd;
		border-radius: 4px;
		font-size: 0.85em;
		color: #1976d2;
		border: 1px solid #bbdefb;
		transition: all 0.2s ease;
	}
	
	.task-parent:hover {
		background: #bbdefb;
		cursor: pointer;
	}
	
	
	.priority-badge {
		padding: 2px 8px;
		border-radius: 12px;
		font-size: 0.8rem;
		font-weight: 500;
		text-transform: uppercase;
		background: #f7f7f8;
		color: #000;
	}
	
	.priority-low i { color: <?= $priority_config['Low']['bg'] ?>; }
	.priority-medium i { color: <?= $priority_config['Medium']['bg'] ?>; }
	.priority-high i { color: <?= $priority_config['High']['bg'] ?>; }
	.priority-urgent i { color: <?= $priority_config['Urgent']['bg'] ?>; }
	
	
	
	.task-actions {
		display: flex;
		gap: 8px;
		margin-left: 16px;
	}
	
	.no-data {
		text-align: center;
		padding: 60px 20px;
		color: #a0aec0;
		font-size: 1.1rem;
	}
	
	.no-data i {
		font-size: 3rem;
		margin-bottom: 16px;
		opacity: 0.3;
	}
	
	@media (max-width: 768px) {
		.task-item {
			flex-direction: column;
			align-items: flex-start;
			gap: 8px;
		}
		
		.task-actions {
			margin-left: 0;
			width: 100%;
			justify-content: flex-end;
		}
		
		.task-parent {
			font-size: 0.8em;
			padding: 3px 6px;
		}
	}
</style>

<!---Modal styles-->
<style>

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
    z-index: 1050;
    display: flex;
    align-items: center;
    overflow-y: auto;
    justify-content: center;
}

.modal-content {
    background: white;
    width: 90%;
    max-width: 1200px;
    max-height: 90vh;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
}

.modal-title-section {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.close-btn {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #666;
    padding: 5px;
}

.close-btn:hover {
    color: #333;
}

.task-id-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.modal-actions {
    margin-left: auto;
    display: flex;
    gap: 5px;
}

.action-btn {
    background: none;
    border: none;
    padding: 5px 8px;
    cursor: pointer;
    border-radius: 4px;
    color: #666;
}

.action-btn:hover {
    background: #f0f0f0;
}

.modal-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    color: #333;
}

.modal-body {
    display: flex;
    flex: 1;
    overflow: hidden;
}

.modal-main {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
}

.modal-sidebar {
    width: 300px;
    background: #f8f9fa;
    padding: 20px;
    border-left: 1px solid #eee;
    overflow-y: auto;
}

.description-section h3 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.task-section {
    margin: 20px 0;
}

.task-section h4 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.task-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.task-section li {
    padding: 5px 0;
    color: #666;
    position: relative;
    padding-left: 20px;
}

.task-section li:before {
    content: "•";
    position: absolute;
    left: 0;
}

.add-sub-btn {
    background: none;
    border: 1px dashed #ccc;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    color: #666;
    margin: 20px 0;
}

.add-sub-btn:hover {
    background: #f0f0f0;
}

.activity-section {
    margin-top: 30px;
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.activity-header h3 {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

.activity-filter {
    display: flex;
    gap: 5px;
}

.filter-btn {
    background: none;
    border: 1px solid #ddd;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.filter-btn.active {
    background: #e3f2fd;
    color: #1976d2;
}

.sidebar-item {
    margin-bottom: 20px;
}

.sidebar-item label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    margin-bottom: 5px;
    text-transform: uppercase;
}

.status-value, .priority-value, .user-info, .component-value, .milestone-value, .due-date-value, .task-type-value, .estimation-value, .spent-time-value, .remaining-time-value {
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-indicator, .priority-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.status-indicator.in-progress {
    background: #2196f3;
}

.priority-indicator.medium {
    background: #ff9800;
}

.user-avatar, .collaborator-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.labels-container {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    align-items: center;
}

.label-tag {
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
}

.label-tag.red {
    background: #ffebee;
    color: #c62828;
}

.label-tag.green {
    background: #e8f5e8;
    color: #2e7d32;
}

.add-label-btn {
    background: none;
    border: 1px dashed #ccc;
    padding: 2px 6px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 11px;
    color: #666;
}

.add-label-btn:hover {
    background: #f0f0f0;
}

.collaborators-list {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Responsive design */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        max-height: 95vh;
    }
    
    .modal-body {
        flex-direction: column;
    }
    
    .modal-sidebar {
        width: 100%;
        border-left: none;
        border-top: 1px solid #eee;
    }
}

.comments-section {
    margin: 20px 0;
    border-top: 1px solid #eee;
    padding-top: 20px;
}

.comments-list {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 20px;
}

.comment-item {
    display: flex;
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 4px;
    background: #f9f9f9;
}

.comment-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
}

.comment-content {
    flex: 1;
	padding-left: 5px;
}

.comment-author {
    font-weight: bold;
    margin-bottom: 5px;
}

.comment-text {
    margin-bottom: 5px;
    white-space: pre-wrap;
}

.comment-meta {
    font-size: 12px;
    color: #666;
}

.comment-actions {
    display: inline-block;
}

.comment-actions .btn {
    padding: 2px 6px;
    font-size: 12px;
    border: none;
    background: none;
}

.comment-actions .btn:hover {
    background: #f0f0f0;
    border-radius: 3px;
}

.comment-edit-form textarea {
    resize: vertical;
    min-height: 60px;
}

.no-comments {
    color: #999;
    text-align: center;
    padding: 20px;
}

.add-comment {
    margin-top: 20px;
}

.add-comment textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical;
    min-height: 80px;
    margin-bottom: 10px;
}

#submitCommentBtn {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

#submitCommentBtn:hover {
    background: #45a049;
}

/* Mention system styles */
.mention-container {
    position: relative;
}

.mention-dropdown {
    position: absolute;
    bottom: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
}

.mention-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
}

.mention-item:hover,
.mention-item.selected {
    background: #f0f8ff;
}

.mention-item:last-child {
    border-bottom: none;
}

.mention-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    margin-right: 8px;
    object-fit: cover;
}

.mention-name {
    font-size: 14px;
    color: #333;
}

.mentioned-user {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 500;
}

</style>

<!--edit task section-->
<style>
.editable-title {
  min-height: 44px;
  padding: 8px 12px;
  border: 1px solid #e9ecef;
  border-radius: 4px;
  font-size: 1.5rem;
  font-weight: 600;
  outline: none;
  position: relative;
  transition: all 0.2s ease;
  background: #f8f9fa;
  cursor: text;
}

.editable-title::after {
  content: "✏️ Click to edit";
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 12px;
  color: #6c757d;
  background: rgba(255,255,255,0.9);
  padding: 2px 6px;
  border-radius: 3px;
  font-weight: normal;
}

.editable-title:focus {
  border-color: #4a89dc;
  background: #fff;
  box-shadow: 0 0 0 2px rgba(74, 137, 220, 0.2);
}

.editable-title:focus::after {
  display: none;
}

.editable-description {
  min-height: 100px;
  padding: 12px;
  border: 1px solid #e9ecef;
  border-radius: 4px;
  outline: none;
  position: relative;
  transition: all 0.2s ease;
  background: #f8f9fa;
  cursor: text;
}

.editable-description::after {
  content: "✏️ Click to edit description";
  position: absolute;
  right: 8px;
  top: 8px;
  font-size: 12px;
  color: #6c757d;
  background: rgba(255,255,255,0.9);
  padding: 2px 6px;
  border-radius: 3px;
  font-weight: normal;
}

.editable-description:focus {
  border-color: #ddd;
  background: #fff;
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.editable-description:focus::after {
  display: none;
}

[contenteditable][data-placeholder]:empty:before {
  content: attr(data-placeholder);
  color: #888;
  font-style: italic;
}
<!--sub task section-->
.sub-tasks-section {
	margin-bottom: 30px;
	border-bottom: 1px solid #eee;
	padding-bottom: 20px;
}

.sub-tasks-title {
	display: flex;
	align-items: center;
	font-size: 16px;
	margin-bottom: 15px;
	color: #555;
}

.sub-tasks-title i {
	margin-right: 10px;
	color: #4a89dc;
}

.sub-tasks-title .badge {
	margin-left: 10px;
	background: #4a89dc;
	color: white;
	padding: 2px 8px;
	border-radius: 10px;
	font-size: 12px;
}

.sub-tasks-list {
	max-height: 300px;
	overflow-y: auto;
}

.sub-task-item {
	display: flex;
	align-items: center;
	padding: 10px;
	margin-bottom: 8px;
	background: #f9f9f9;
	border-radius: 4px;
	transition: all 0.3s;
}

.sub-task-item:hover {
	background: #f0f0f0;
}

.sub-task-status {
	width: 12px;
	height: 12px;
	border-radius: 50%;
	margin-right: 10px;
	flex-shrink: 0;
}

.sub-task-content {
	flex-grow: 1;
}

.sub-task-title {
	font-weight: 500;
	margin-bottom: 3px;
}

.sub-task-meta {
	font-size: 12px;
	color: #777;
	display: flex;
	gap: 15px;
}

.sub-task-meta .priority-high {
	color: #dc3545;
	font-weight: 600;
}

.sub-task-meta .priority-medium {
	color: #ffc107;
	font-weight: 600;
}

.sub-task-meta .priority-low {
	color: #28a745;
	font-weight: 600;
}

.sub-task-status-header {
	display: flex;
	align-items: center;
	padding: 8px 0;
	margin: 15px 0 8px 0;
	border-bottom: 1px solid #eee;
	font-weight: 600;
	color: #555;
}

.sub-task-status-header .status-indicator {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	margin-right: 8px;
}

.sub-task-status-header .status-label {
	font-size: 13px;
	margin-right: 5px;
}

.sub-task-status-header .status-count {
	font-size: 12px;
	color: #888;
	font-weight: normal;
}

.sub-task-item.completed {
	opacity: 0.7;
}

.sub-task-item.completed .sub-task-title {
	text-decoration: line-through;
}

.sub-tasks-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 15px;
}

.sub-tasks-filter {
	min-width: 120px;
}

.sub-tasks-filter select {
	font-size: 12px;
	padding: 4px 8px;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.no-sub-tasks {
	color: #999;
	text-align: center;
	padding: 20px;
	font-style: italic;
}
</style>
			

<!-- Sub Issue Modal specific styles -->
<style>
#subIssueModal .modal-dialog {
	max-width: 40%;
	width: 40%;
}

#subIssueModal .modal-content {
	border-radius: 10px;
	border: 2px solid #4a89dc;
}

#subIssueModal .panel-title {
	color: #4a89dc;
	font-weight: 600;
}

/* Make the parent issue field visually distinct */
input[name="parent_issue"] {
	background-color: #f8f9fa;
	border: 1px dashed #ccc;
}
.activity-content {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.comments-section, .sub-tasks-section {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.comments-title {
    display: flex;
    align-items: center;
    font-size: 16px;
    margin-bottom: 15px;
    color: #555;
}

.comments-title i {
    margin-right: 10px;
    color: #6c757d;
}
</style>			


<!--Comments -->
<style>	
.activity-content {
	display: flex;
	flex-direction: column;
	gap: 25px;
}

.comments-section, .sub-tasks-section {
	background: white;
	padding: 15px;
	border-radius: 8px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.comments-title {
	display: flex;
	align-items: center;
	font-size: 16px;
	margin-bottom: 15px;
	color: #555;
}

.comments-title i {
	margin-right: 10px;
	color: #6c757d;
}


.select2-container--bootstrap .select2-selection,.select2-container--bootstrap .select2-selection--multiple .select2-selection__rendered {
    -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
    box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
	background: #f8f9fa;
    border: none;
    border-radius: none;
    color: #555;
    font-size: 14px;
    outline: 0;
}

.search-container .input-group {
    display: flex;
    align-items: center;
}

.search-container .input-group-addon {
    background: #f8f9fa;
    border: 1px solid #ddd;
    padding: 8px 12px;
    color: #666;
}

#taskSearchInput {
    border: 1px solid #ddd;
    padding: 8px 12px;
    font-size: 14px;
    flex: 1;
    outline: none;
}

#taskSearchInput:focus {
    border-color: #4a89dc;
    box-shadow: 0 0 0 2px rgba(74, 137, 220, 0.2);
}
</style>

<div class="row" style="height: calc(108vh);">
    <div class="col-md-3">
        <?php include 'sidebar.php'; ?>
    </div>
    <div class="col-md-9" style="height: 100%; overflow-y: auto;">
        <div class="issue-tracker">
            <!-- Header -->
            <div class="tracker-header">
                <div class="row d-flex justify-content-between align-items-center">
					<div class="col-md-6">
						<h3 class="tracker-title">
							<i class="fas fa-tasks"></i>
							My Issues
						</h3>
					</div>
					<div class="col-md-6">
						<div class="header-actions" style="text-align:end">
						<span id="dataInfo" class="text-muted" style="font-size: 12px;"></span>
						<button id="loadAllBtn" class="btn btn-outline-primary" style="display: none;">
							Load All
						</button>
						</div>
                    </div>
                </div>
                
				<!-- 🔍 Search and Filter Section -->
				<div class="search-container" style="margin: 20px 0;">
					<div class="row">
						<div class="col-md-8">
							<div class="input-group" style="box-shadow: 0 2px 6px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
								<input type="text" 
									   id="taskSearchInput" 
									   class="form-control" 
									   placeholder="🔍 Search by ID, Title, or Description..." 
									   style="border: none; padding: 12px 15px; font-size: 14px;">
								<span class="input-group-addon" 
									  style="background: #fff; border: none; padding: 0 15px; display: flex; align-items: center; cursor: pointer; transition: 0.3s;"
									  onclick="clearSearch()"
									  onmouseover="this.style.color='#e74c3c';"
									  onmouseout="this.style.color='#666';">
									<i class="fas fa-times" id="clearSearchIcon"></i>
								</span>
							</div>
						</div>
						<div class="col-md-4">
							<div class="milestone-filter" style="box-shadow: 0 2px 6px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
								<select id="milestoneFilter" class="form-control" data-plugin-selectTwo data-placeholder="🏁 Search Milestones..." data-width="100%" style="border: none; font-size: 14px; background: #fff;">
									<option value="all">🏁 All Milestones</option>
									<?php foreach($this->db->get('tracker_milestones')->result() as $milestone): ?>
										<option value="<?= $milestone->id ?>"><?= $milestone->title ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
					<small class="text-muted" style="margin-top: 8px; display: block; font-size: 12px; color: #888;">
						Search across <b>Task ID</b>, <b>Title</b>, and <b>Description</b> | Filter by <b>Milestone</b>
					</small>
				</div>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="panel-body">
                <div style="text-align: center; padding: 60px 20px; color: #666;">
                    <div class="spinner" style="margin: 0 auto 20px; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <h4>Loading Issues...</h4>
                    <p>Please wait while we fetch your issues</p>
                </div>
            </div>

            <!-- Issues Container -->
            <div id="issuesContainer" class="panel-body" style="display: none;">
                <!-- Issues will be loaded here -->
            </div>

           <!-- Task Details Modal -->
			<div id="taskModal" class="modal-overlay" style="display: none;">
				<div class="modal-content">
					<div class="modal-header">
						<div class="modal-title-section">
							<button class="close-btn" onclick="closeTaskModal()">&times;</button>
							<span class="task-id-badge" id="modalTaskId">Loading...</span>
						<button class="btn btn-danger btn-sm" onclick="deleteIssue()" style="margin-left: auto;">
							<i class="fas fa-trash"></i> Delete
						</button>
						</div>
						<div id="parentTaskInfo" style="display: none; margin-bottom: 10px; padding: 8px 12px; background: #e3f2fd; border-radius: 4px; font-size: 0.9em; color: #1976d2; cursor: pointer;" onclick="openParentTask()">
							<i class="fas fa-arrow-up" style="margin-right: 5px;"></i>
							<strong>Parent Task:</strong> <span id="parentTaskDetails"></span>
						</div>
						<div class="editable-title" id="modalTaskTitle" contenteditable="true" data-placeholder="<?= translate('task_title') ?>"></div>
					</div>

					<div class="modal-body">
						<div class="modal-main">
							<div class="task-content">
								<div class="description-section">
									<div id="modalTaskDescription" class="editable-description" contenteditable="true" data-placeholder="<?= translate('add_description') ?>"></div>
								</div>
							</div>
							
							<div class="activity-section">
								<div class="activity-header">
									<h3><i class="fas fa-chart-line"></i> <?= translate('activity') ?></h3>
									<div class="add-sub-issue">
										<button type="button" class="btn btn-primary" id="addSubIssueBtn">
											<i class="fas fa-plus-circle"></i> <?= translate('add_sub_issue') ?>
										</button>
									</div>
								</div>
								
								<div class="activity-content">
									<!-- Sub-tasks Section -->
									<div class="sub-tasks-section">
										<div class="sub-tasks-header">
											<h4 class="sub-tasks-title">
												<i class="fas fa-tasks"></i> <?= translate('sub_tasks') ?>
												<span class="badge" id="subTasksCount">0</span>
											</h4>
											<div class="sub-tasks-filter">
												<select id="subTaskStatusFilter" class="form-control form-control-sm">
													<option value="all">All Status</option>
													<option value="todo">To Do</option>
													<option value="in_progress">In Progress</option>
													<option value="in_review">In Review</option>
													<option value="completed">Completed</option>
													<option value="hold">On Hold</option>
													<option value="canceled">Canceled</option>
												</select>
											</div>
										</div>
										<div class="sub-tasks-list" id="subTasksList">
											<div class="no-sub-tasks"><?= translate('no_sub_tasks') ?></div>
										</div>
									</div>
									
									<!-- Comments Section -->
									<div class="comments-section">
										<div class="comments-list" id="commentsList">
											<div class="no-comments"><?= translate('no_comments') ?></div>
										</div>
										<div class="add-comment">
											<div class="mention-container" style="position: relative;">
												<textarea id="newCommentText" placeholder="<?= translate('add_comment') ?>... (Type @ to mention someone)" rows="3"></textarea>
												<div id="mentionDropdown" class="mention-dropdown" style="display: none;"></div>
											</div>
											<button id="submitCommentBtn" class="btn btn-primary"><?= translate('post_comment') ?></button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="modal-sidebar">
							<!-- Status -->
							<div class="sidebar-item">
								<label><i class="fas fa-columns"></i> <?= translate('status') ?></label>
								<select id="modalStatusSelect" class="form-control" onchange="updateTaskField('task_status', this.value)">
									<?php foreach($statuses as $value => $label): ?>
										<?php if($value !== 'completed'): ?>
											<option value="<?= $value ?>"><?= $label ?></option>
										<?php endif; ?>
									<?php endforeach; ?>
								</select>
							</div>
							
							<!-- Priority -->
							<div class="sidebar-item">
								<label><i class="fas fa-exclamation-circle"></i> <?= translate('priority') ?></label>
								<select id="modalPrioritySelect" class="form-control" onchange="updateTaskField('priority_level', this.value)">
									<?php foreach($priorities as $value => $label): ?>
										<option value="<?= $value ?>"><?= $label ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							
							<!-- Category -->
							<div class="sidebar-item">
								<label><i class="fas fa-list-alt"></i> <?= translate('category') ?></label>
								<select id="modalCategorySelect" class="form-control" onchange="updateTaskField('category', this.value)">
									<option value="Milestone">Milestone</option>
									<option value="Incident">Incident</option>
									<option value="Customer Query">Customer Query</option>
									<option value="Explore">Explore</option>
									<option value="EMP Request">EMP Request</option>
								</select>
							</div>
							
							<!-- Created By -->
							<div class="sidebar-item">
								<label><i class="fas fa-user-plus"></i> <?= translate('created_by') ?></label>
								<div class="user-info">
									<img id="modalCreatedByAvatar" src="" alt="User Avatar" class="user-avatar">
									<span id="modalCreatedBy">Loading...</span>
								</div>
							</div>
							
							<!-- Assignee -->
							<div class="sidebar-item">
								<label><i class="fas fa-user-check"></i> <?= translate('assignee') ?></label>
								<select id="modalAssigneeSelect" class="form-control" onchange="updateTaskField('assigned_to', this.value)">
									<option value=""><?= translate('select_assignee') ?></option>
									<?php
									$this->db->select('s.id, s.name');
									$this->db->from('staff AS s');
									$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
									$this->db->where('lc.active', 1);
									$this->db->where_not_in('lc.role', [1, 9, 10, 11, 12]);
									$this->db->order_by('s.name', 'ASC');
									$staff_query = $this->db->get();
									foreach ($staff_query->result() as $staff): ?>
										<option value="<?= $staff->id ?>"><?= $staff->name ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							
							<!-- Coordinator -->
							<div class="sidebar-item">
								<label><i class="fas fa-user-tie"></i> Coordinator</label>
								<select id="modalCoordinatorSelect" class="form-control" onchange="updateTaskField('coordinator', this.value)">
									<option value=""><?= translate('select_coordinator') ?></option>
									<?php
									$this->db->select('s.id, s.name');
									$this->db->from('staff AS s');
									$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
									$this->db->where('lc.active', 1);
									$this->db->where_not_in('lc.role', [1, 9, 10, 11, 12]);
									$this->db->order_by('s.name', 'ASC');
									$coordinator_query = $this->db->get();
									foreach ($coordinator_query->result() as $coordinator): ?>
										<option value="<?= $coordinator->id ?>"><?= $coordinator->name ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							
							
							<!-- Parent Task -->
							<div class="sidebar-item">
								<label><i class="fas fa-arrow-up"></i> <?= translate('parent_task') ?></label>
								<select id="modalParentTaskSelect" class="form-control" onchange="updateTaskField('parent_issue', this.value)" data-plugin-selectTwo data-placeholder="Search and select parent task..." data-width="100%">
									<option value=""><?= translate('no_parent') ?></option>
								</select>
							</div>
							
							<!-- Labels -->
							<div class="sidebar-item">
								<label><i class="fas fa-tags"></i> <?= translate('labels') ?></label>
								<?php
							echo form_dropdown(
								"modalLabels[]", 
								$this->app_lib->getSelectList('task_labels'), 
								[], 
								"id='modalLabelsSelect' class='form-control' multiple 
								 data-plugin-selectTwo 
								 data-placeholder='".translate('select_labels')."' 
								 data-width='100%'
								 onchange=\"updateTaskField('label', Array.from(this.selectedOptions).map(o => o.value).join(','))\""
							);
							?>
							</div>
							
							<!-- SOP -->
							<div class="sidebar-item">
								<label><i class="fas fa-file-alt"></i> SOP</label>
								<?php
									$sop_options = [];
									$sops = $this->db->select('id, title')
													->from('sop')
													->get()->result();
									foreach ($sops as $sop) {
										$sop_options[$sop->id] = $sop->title;
									}
									echo form_dropdown(
										"modalSopIds[]", 
										$sop_options, 
										[], 
										"id='modalSopIdsSelect' class='form-control' multiple 
										 data-plugin-selectTwo 
										 data-placeholder='Select SOP IDs' 
										 data-width='100%'
										 onchange=\"updateTaskField('sop_ids', Array.from(this.selectedOptions).map(o => o.value).join(','))\""
									);
								?>
							</div>
							
							<!-- Component -->
							<div class="sidebar-item">
								<label><i class="fas fa-puzzle-piece"></i> <?= translate('initiatives') ?></label>
								<select id="modalComponentSelect" class="form-control" onchange="updateTaskField('component', this.value)">
							<option value=""><?= translate('select_component') ?></option>
							<?php foreach($this->db->get('tracker_components')->result() as $comp): ?>
								<option value="<?= $comp->id ?>"><?= $comp->title ?></option>
							<?php endforeach; ?>
						</select>
							</div>
							
							<!-- Milestone -->
							<div class="sidebar-item">
								<label><i class="fas fa-flag-checkered"></i> <?= translate('milestone') ?></label>
								<select id="modalMilestoneSelect" class="form-control" onchange="updateTaskField('milestone', this.value)">
							<option value=""><?= translate('select_milestone') ?></option>
							<?php foreach($this->db->get('tracker_milestones')->result() as $mile): ?>
								<option value="<?= $mile->id ?>"><?= $mile->title ?></option>
							<?php endforeach; ?>
						</select>
							</div>
							
							<!-- Task Type -->
							<div class="sidebar-item">
								<label><i class="fa-solid fa-database"></i> <?= translate('task_type') ?></label>
								<select id="modalTaskTypeSelect" class="form-control" onchange="updateTaskField('task_type', this.value)">
									<option value=""><?= translate('select_task_type') ?></option>
									<?php foreach($this->db->get('task_types')->result() as $type): ?>
										<option value="<?= $type->id ?>"><?= $type->name ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							
							<!-- Due Date -->
							<div class="sidebar-item">
								<label><i class="fas fa-calendar-alt"></i> <?= translate('due_date') ?></label>
								<input type="date" id="modalDueDateInput" class="form-control" onchange="updateTaskField('estimated_end_time', this.value)">
							</div>
							
							<!-- Time Tracking -->
							<div class="sidebar-item">
								<label><i class="fas fa-clock"></i> <?= translate('estimation') ?></label>
								<input type="number" step="0.1" id="modalEstimationInput" class="form-control" placeholder="Hours" onchange="updateTaskField('estimation_time', this.value)">
							</div>
							<div class="sidebar-item">
								<label><i class="fas fa-hourglass-half"></i> <?= translate('spent_time') ?></label>
								<div class="spent-time-value" id="modalSpentTime">Loading...</div>
							</div>
							<div class="sidebar-item">
								<label><i class="fas fa-hourglass-end"></i> <?= translate('remaining_time') ?></label>
								<div class="remaining-time-value" id="modalRemainingTime">Loading...</div>
							</div>
							<!-- Customer Query Data -->
							<div class="sidebar-item" id="customerQuerySection" style="display: none;">
								<label><i class="fas fa-headset"></i> Customer Query Details</label>
								<div class="customer-query-info">
									<div class="query-field" style="margin-bottom: 8px;">
										<strong>Source:</strong> <span id="modalQuerySource">N/A</span>
									</div>
									<div class="query-field" style="margin-bottom: 8px;">
										<strong>Contact:</strong> <span id="modalQueryContact">N/A</span>
									</div>
									<div class="query-field" style="margin-bottom: 8px;">
										<strong>Requested At:</strong> <span id="modalQueryRequestedAt">N/A</span>
									</div>
									<div class="query-field" id="modalQueryBodySection" style="display: none;">
										<strong>Request Body:</strong>
										<div id="modalQueryBody" style="margin-top: 5px; padding: 8px; background: #f8f9fa; border-radius: 4px; font-size: 12px; max-height: 100px; overflow-y: auto;">N/A</div>
									</div>
								</div>
							</div>
							<!-- Accepted Details -->
							<div class="sidebar-item" id="acceptedDetailsSection" style="display: none;">
								<label><i class="fas fa-check-circle"></i> Accepted Details</label>
								<div id="acceptedDetails" style="font-size: 12px; color: #666; line-height: 1.4;">
									<div id="acceptedBy"></div>
									<div id="acceptedAt"></div>
								</div>
							</div>
							
							
						</div>
					</div>
				</div>
			</div>

			<!-- Sub-Task Modal (Same style as main modal) -->
			<div id="subTaskModal" class="zoom-anim-dialog modal-block mfp-hide modal-block-lg">
				<section class="panel">
					<div class="panel-heading">
						<h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?= translate('add_sub_issue') ?></h4>
					</div>
					<?php echo form_open('tracker/save_issue', array('class' => 'form-horizontal', 'method' => 'POST')); ?>
					<input type="hidden" name="parent_issue" id="parentIssueId" value="">
					<input type="hidden" name="milestone" id="parentMilestoneId" value="">
					<div class="panel-body">
						<!-- Department and Category -->
						<div class="row form-group">
							<div class="col-md-6">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-building"></i>
									</span>
									<?php
										$department_options = ['' => translate('select_department')];
										foreach ($this->db->get('tracker_departments')->result() as $depts) {
											$department_options[$depts->identifier] = $depts->title;
										}
										echo form_dropdown(
											'department',
											$department_options,
											'',
											"class='form-control' id='departmentSelect' required 
											 data-plugin-selectTwo 
											 data-placeholder='".translate('select_department')."'
											 data-width='100%'"
										);
									?>
								</div>
								<span class="error"><?= form_error('department') ?></span>
							</div>
							<div class="col-md-6">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-list-alt"></i>
									</span>
									<select name="category" class="form-control" required data-plugin-selectTwo data-width="100%">
										<option value="Milestone">Milestone</option>
										<option value="Incident">Incident</option>
										<option value="Customer Query">Customer Query</option>
										<option value="Explore">Explore</option>
										<option value="EMP Request">EMP Request</option>
									</select>
								</div>
								<span class="error"><?= form_error('category') ?></span>
							</div>
						</div>

						<!-- Title -->
						<div class="row form-group">
							<div class="col-md-12">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-heading"></i>
									</span>
									<input type="text" 
										   class="form-control" 
										   id="task_title" 
										   name="task_title" 
										   placeholder="<?= translate('enter_title') ?>" 
										   required>
								</div>
								<span class="error"><?= form_error('task_title') ?></span>
							</div>
						</div>

						<!-- Description -->
						<div class="row form-group">
							<div class="col-md-12">
								<textarea name="task_description" id="task_description" class="summernote"></textarea>
								<span class="error"><?= form_error('task_description') ?></span>
							</div>
						</div>
						<!-- Customer Query Fields (shown only when category is Customer Query) -->
						<div id="customerQueryFields" style="display: none;">
							<div class="row form-group">
								<div class="col-md-4">
									<div class="input-group">
										<span class="input-group-addon">
											<i class="fas fa-link"></i>
										</span>
										<select name="source" class="form-control" data-plugin-selectTwo data-width="100%">
											<option value="">Select Source</option>
											<option value="Email">Email</option>
											<option value="Phone">Phone</option>
											<option value="Chat">Chat</option>
											<option value="Portal">Portal</option>
											<option value="Other">Other</option>
										</select>
									</div>
									<span class="error"><?= form_error('source') ?></span>
								</div>
								<div class="col-md-4">
									<div class="input-group">
										<span class="input-group-addon">
											<i class="fas fa-address-book"></i>
										</span>
										<input type="text" name="contact_info" class="form-control" placeholder="Contact Information (Email/Phone)">
									</div>
									<span class="error"><?= form_error('contact_info') ?></span>
								</div>
								<div class="col-md-4">
									<div class="input-group">
										<span class="input-group-addon">
											<i class="fas fa-calendar-alt"></i>
										</span>
										<input type="datetime-local" name="requested_at" class="form-control" placeholder="Requested At">
									</div>
									<span class="error"><?= form_error('requested_at') ?></span>
								</div>
							</div>
							<div class="row form-group">
								<div class="col-md-12">
									<label>Request/Mail Body:</label>
									<textarea name="request_body" class="form-control" rows="4" placeholder="Enter the original request or mail body..."></textarea>
									<span class="error"><?= form_error('request_body') ?></span>
								</div>
							</div>
						</div>
						<br>
						<!-- Status, Priority, Assignee -->
						<div class="row form-group">
							<div class="col-md-3">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-columns"></i>
									</span>
									<select name="task_status" class="form-control" required data-plugin-selectTwo data-width="100%">
										<?php foreach($statuses as $value => $label): ?>
											<?php if($value !== 'completed'): ?>
												<option value="<?= $value ?>"><?= $label ?></option>
											<?php endif; ?>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							
							<div class="col-md-3">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-exclamation-circle"></i>
									</span>
									<select name="priority_level" class="form-control" data-plugin-selectTwo data-width="100%">
										<?php foreach($priorities as $value => $label): ?>
											<option value="<?= $value ?>"><?= $label ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							
							<!-- Assignee -->
							<div class="col-md-2">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-user"></i>
									</span>
									<?php
									$this->db->select('s.id, s.name');
									$this->db->from('staff AS s');
									$this->db->join('login_credential AS lc', 'lc.user_id = s.id');
									$this->db->where('lc.active', 1);   // only active users
									$this->db->where_not_in('lc.role', [1, 9, 10, 11, 12]);   // exclude super admin, etc.
									$this->db->where_not_in('s.id', [49]);  
									$this->db->order_by('s.name', 'ASC');
									$query = $this->db->get();

									$staffArray = ['' => 'Select']; // <-- default first option
									foreach ($query->result() as $row) {
										$staffArray[$row->id] = $row->name;
									}
									
									// Get logged-in user staff_id (adjust session key if different)
									$logged_in_user = get_loggedin_user_id();;

									echo form_dropdown(
										"assigned_to",
										$staffArray,
										$logged_in_user, // Default selected value
										"class='form-control' required
										data-plugin-selectTwo
										data-placeholder='".translate('assign_to')."'
										data-width='100%'"
									);
									?>
								</div>

								<span class="error"><?= form_error('assigned_to') ?></span>
							</div>

							<!-- Coordinator -->
							<div class="col-md-2">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-user-tie"></i>
									</span>
									<?php
									// Get default coordinator based on logged-in user's role
									$default_coordinator = '';
									$logged_in_role = loggedin_role_id();
									$logged_in_dept = $this->db->select('department')->from('staff')->where('id', $logged_in_user)->get()->row();
									
									if ($logged_in_role == 3) {
										// Role 3 coordinator is role 3 (self)
										$default_coordinator = $logged_in_user;
									} elseif ($logged_in_role == 4 && $logged_in_dept) {
										// Find role 8 from same department, fallback to role 3
										$coordinator = $this->db->select('s.id')
											->from('staff s')
											->join('login_credential lc', 'lc.user_id = s.id')
											->where('lc.role', 8)
											->where('s.department', $logged_in_dept->department)
											->where('lc.active', 1)
											->get()->row();
										if ($coordinator) {
											$default_coordinator = $coordinator->id;
										} else {
											// Fallback to role 3
											$coordinator = $this->db->select('s.id')
												->from('staff s')
												->join('login_credential lc', 'lc.user_id = s.id')
												->where('lc.role', 3)
												->where('lc.active', 1)
												->get()->row();
											if ($coordinator) $default_coordinator = $coordinator->id;
										}
									} elseif (in_array($logged_in_role, [5, 8])) {
										// Find role 3
										$coordinator = $this->db->select('s.id')
											->from('staff s')
											->join('login_credential lc', 'lc.user_id = s.id')
											->where('lc.role', 3)
											->where('lc.active', 1)
											->get()->row();
										if ($coordinator) $default_coordinator = $coordinator->id;
									}
									
									echo form_dropdown(
										"coordinator",
										$staffArray,
										$default_coordinator,
										"class='form-control'
										data-plugin-selectTwo
										data-placeholder='Select Coordinator'
										data-width='100%'"
									);
									?>
								</div>
								<span class="error"><?= form_error('coordinator') ?></span>
							</div>

							
							
							<!-- Component -->
							<div class="col-md-2">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-puzzle-piece"></i>
									</span>
									<?php
										$tsk_component = ['' => translate('initiatives')];
										foreach ($this->db->get('tracker_components')->result() as $task_component) {
											$tsk_component[$task_component->id] = $task_component->title;
										}
										echo form_dropdown(
											'component',
											$tsk_component,
											'',
											"class='form-control' data-plugin-selectTwo data-width='100%'"
										);
									?>
								</div>
								<span class="error"><?= form_error('component') ?></span>
							</div>
						</div>

						<!-- Estimation -->
						<div class="row form-group">
							<!-- Labels -->
							<div class="col-md-6">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-tags"></i>
									</span>
									<?php
										echo form_dropdown(
											"label[]", 
											$this->app_lib->getSelectList('task_labels'), 
											[], 
											"class='form-control' multiple required 
											 data-plugin-selectTwo 
											 data-placeholder='".translate('select_labels')."' 
											 data-width='100%'"
										);
									?>
								</div>
								<span class="error"><?= form_error('label[]') ?></span>
							</div>
							
							<!-- Estimation -->
							<div class="col-md-3">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-clock"></i>
									</span>
									<input type="number" step="0.1" name="estimation_time" class="form-control" 
										   placeholder="<?= translate('estimated_time') ?>..." required />
								</div>
								<span class="error"><?= form_error('estimation_time') ?></span>
							</div>
							<!-- Due Date -->
							<div class="col-md-3">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fas fa-calendar-alt"></i>
									</span>
									<input type="date" 
										   name="estimated_end_time" 
										   class="form-control"
										   placeholder="<?= translate('select_due_date') ?>"
										   min="<?= date('Y-m-d') ?>"
										   onfocus="(this.type='date')"
										   onblur="if(!this.value)this.type='text'">
								</div>
								<span class="error"><?= form_error('estimated_end_time') ?></span>
							</div>
						</div>
						
						<div class="row form-group">
							<!-- SOP IDs -->
							<div class="col-md-9">
								<div class="input-group">
									<span class="input-group-addon">
										 <i class="fas fa-file-alt"></i>
									</span>
									 <?php
										$sop_options = [];
										$sops = $this->db->select('id, title')
														->from('sop')
														->get()->result();
										foreach ($sops as $sop) {
											$sop_options[$sop->id] = $sop->title;
										}
										echo form_dropdown(
											'sop_ids[]',
											$sop_options,
											'',
											"class='form-control' multiple 
											 data-plugin-selectTwo 
											 data-placeholder='Select SOP IDs'
											 data-width='100%'"
										);
									?>
								</div>
								<span class="error"><?= form_error('sop_ids[]') ?></span>
							</div>


							<!-- task types -->
							<div class="col-md-3">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa-solid fa-database"></i>
									</span>
									<?php
										$task_type = ['' => translate('task_types')];
										$task_type += $this->app_lib->getSelectList('task_types');
										echo form_dropdown(
											'task_type',
											$task_type,
											'',
											"class='form-control' data-plugin-selectTwo data-width='100%'"
										);
									?>
								</div>
								<span class="error"><?= form_error('milestone') ?></span>
							</div>
						</div>
						
					</div>
					<footer class="panel-footer">
						<div class="row">
							<div class="col-md-12 text-right">
								<button type="submit" class="btn btn-primary">
									<i class="fas fa-plus-circle"></i> <?= translate('add_sub_issue') ?>
								</button>
								<button type="button" class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
							</div>
						</div>
					</footer>
					<?php echo form_close(); ?>
				</section>
			</div>

			<script>
			// Function to open sub-task modal
			function openSubIssueModal(parentId) {
				// Get parent task milestone from the modal
				const parentMilestone = document.getElementById('modalMilestoneSelect').value;
				
				$('#parentIssueId').val(parentId);
				$('#parentMilestoneId').val(parentMilestone);
				$.magnificPopup.open({
					items: {
						src: '#subTaskModal',
						type: 'inline'
					}
				});
				
				// Setup category-milestone dependency
				setupCategoryMilestoneDependency();
				
				// Setup customer query fields visibility
				setupCustomerQueryFields();
				
				// Auto-select department based on user's department
				<?php 
				$user_id = get_loggedin_user_id();
				$user_dept = $this->db->select('sd.name as dept_name')
								->from('staff s')
								->join('staff_department sd', 's.department = sd.id', 'left')
								->where('s.id', $user_id)
								->get()->row();
				if ($user_dept && $user_dept->dept_name): ?>
				var userDeptName = '<?= strtolower($user_dept->dept_name) ?>';
				var bestMatch = '';
				var bestScore = 0;
				
				$('#departmentSelect option').each(function() {
					var optionText = $(this).text().toLowerCase();
					if (optionText && optionText !== 'select department') {
						var score = calculateSimilarity(userDeptName, optionText);
						if (score >= 0.5 && score > bestScore) {
							bestScore = score;
							bestMatch = $(this).val();
						}
					}
				});
				
				if (bestMatch) {
					$('#departmentSelect').val(bestMatch).trigger('change');
				}
				<?php endif; ?>
			}
			
			// Setup category-milestone dependency for sub-task modal
			function setupCategoryMilestoneDependency() {
				const categorySelect = $('#subTaskModal select[name="category"]');
				const milestoneSelect = $('#subTaskModal select[name="milestone"]');
				
				// Function to update milestone requirement
				function updateMilestoneRequirement() {
					const selectedCategory = categorySelect.val();
					if (selectedCategory === 'Milestone') {
						milestoneSelect.prop('required', true);
					} else {
						milestoneSelect.prop('required', false);
					}
				}
				
				// Initial check
				updateMilestoneRequirement();
				
				// Listen for category changes
				categorySelect.on('change', updateMilestoneRequirement);
			}
			
			// Setup customer query fields visibility
			function setupCustomerQueryFields() {
				const categorySelect = $('#subTaskModal select[name="category"]');
				const customerQueryFields = $('#customerQueryFields');
				
				// Function to toggle customer query fields
				function toggleCustomerQueryFields() {
					const selectedCategory = categorySelect.val();
					if (selectedCategory === 'Customer Query') {
						customerQueryFields.show();
						// Make fields required when visible
						$('#customerQueryFields select[name="source"]').prop('required', true);
						$('#customerQueryFields input[name="contact_info"]').prop('required', true);
						$('#customerQueryFields input[name="requested_at"]').prop('required', true);
					} else {
						customerQueryFields.hide();
						// Remove required when hidden
						$('#customerQueryFields select[name="source"]').prop('required', false);
						$('#customerQueryFields input[name="contact_info"]').prop('required', false);
						$('#customerQueryFields input[name="requested_at"]').prop('required', false);
					}
				}
				
				// Initial check
				toggleCustomerQueryFields();
				
				// Listen for category changes
				categorySelect.on('change', toggleCustomerQueryFields);
			}

			// Function to calculate string similarity for department matching
			function calculateSimilarity(str1, str2) {
				if (str1 === str2) return 1;
				if (str1.length === 0 || str2.length === 0) return 0;
				
				// Check if one string contains the other
				if (str1.includes(str2) || str2.includes(str1)) {
					return Math.max(str2.length / str1.length, str1.length / str2.length);
				}
				
				// Simple word matching
				var words1 = str1.split(' ');
				var words2 = str2.split(' ');
				var matches = 0;
				
				for (var i = 0; i < words1.length; i++) {
					for (var j = 0; j < words2.length; j++) {
						if (words1[i] === words2[j] || words1[i].includes(words2[j]) || words2[j].includes(words1[i])) {
							matches++;
							break;
						}
					}
				}
				
				return matches / Math.max(words1.length, words2.length);
			}
			
			// Submit handler for sub-task form
			$('#subTaskForm').submit(function(e) {
				e.preventDefault();
				const form = $(this);
				const submitBtn = form.find('button[type="submit"]');
				
				submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing');
				
				$.ajax({
					url: form.attr('action'),
					type: 'POST',
					data: form.serialize(),
					success: function(response) {
						if(response.success) {
							$.magnificPopup.close();
							loadSubTasks($('#parentIssueId').val()); // Refresh sub-tasks list
							showToast('success', response.message);
						} else {
							showToast('error', response.message);
						}
					},
					error: function() {
						showToast('error', 'An error occurred');
					},
					complete: function() {
						submitBtn.prop('disabled', false).html('<i class="fas fa-plus-circle"></i> <?= translate('add_sub_issue') ?>');
					}
				});
			});
			</script>
			
			<script>
			const statusConfig = <?= json_encode($status_config) ?>;
			const priorityConfig = <?= json_encode($priority_config) ?>;
			const staffList = <?= json_encode($staff_lookup) ?>;
			const labelList = <?= json_encode($label_lookup) ?>;

			document.getElementById('modalTaskTitle').addEventListener('blur', function() {
			  updateTaskField('task_title', this.textContent);
			});

			document.getElementById('modalTaskDescription').addEventListener('blur', function() {
			  updateTaskField('task_description', this.innerHTML); // Use innerHTML to preserve formatting
			});

			// Update the openTaskModal function to load comments
			function openTaskModal(taskId) {
				document.getElementById('taskModal').style.display = 'flex';
				document.body.style.overflow = 'hidden';
				resetModalToLoading();
				
				// Load task details
				fetch(`<?= base_url('tracker/get_task_details/') ?>${taskId}`, {
					headers: {'X-Requested-With': 'XMLHttpRequest'}
				})
				.then(response => {
					if (!response.ok) throw new Error('Network response was not ok');
					return response.json();
				})
				.then(data => {
					if (data.error) throw new Error(data.error);
					populateModal(data, taskId);
					setupEditableFields();
					loadSubTasks(taskId);
					loadComments(data.unique_id);
				})
				.catch(err => {
					console.error('Error:', err);
					showModalError(err.message || 'Failed to load task details');
				});
			}
			
			// Store all sub-tasks globally for filtering
			let allSubTasks = [];
			
			// Sub-tasks loader with status categorization
			function loadSubTasks(taskId) {
				fetch(`<?= base_url('tracker/get_sub_tasks/') ?>${taskId}`, {
					headers: {'X-Requested-With': 'XMLHttpRequest'}
				})
				.then(response => response.json())
				.then(subTasks => {
					allSubTasks = subTasks || [];
					renderSubTasks(allSubTasks);
					
					// Setup filter event listener
					document.getElementById('subTaskStatusFilter').onchange = function() {
						filterSubTasks(this.value);
					};
				})
				.catch(err => {
					console.error('Error loading sub-tasks:', err);
					document.getElementById('subTasksList').innerHTML = 
						'<div class="no-sub-tasks">Error loading sub-tasks</div>';
				});
			}
			
			function renderSubTasks(subTasks, filterStatus = 'all') {
				const container = document.getElementById('subTasksList');
				const counter = document.getElementById('subTasksCount');
				
				container.innerHTML = '';
				counter.textContent = '0';
				
				if (!subTasks || subTasks.length === 0) {
					container.innerHTML = '<div class="no-sub-tasks">No sub-tasks yet</div>';
					return;
				}
				
				// Filter tasks if needed
				let filteredTasks = subTasks;
				if (filterStatus !== 'all') {
					filteredTasks = subTasks.filter(task => (task.task_status || 'todo') === filterStatus);
				}
				
				counter.textContent = filteredTasks.length;
				
				// Group sub-tasks by status
				const groupedTasks = {};
				filteredTasks.forEach(task => {
					const status = task.task_status || 'todo';
					if (!groupedTasks[status]) {
						groupedTasks[status] = [];
					}
					groupedTasks[status].push(task);
				});
				
				// Define status order and labels
				const statusOrder = ['todo', 'in_progress', 'in_review', 'completed', 'hold', 'canceled'];
				const statusLabels = {
					'todo': 'To Do',
					'in_progress': 'In Progress', 
					'in_review': 'In Review',
					'completed': 'Completed',
					'hold': 'On Hold',
					'canceled': 'Canceled'
				};
				
				// Render grouped sub-tasks
				statusOrder.forEach(status => {
					if (groupedTasks[status] && groupedTasks[status].length > 0) {
						// Create status group header (only if showing all statuses)
						if (filterStatus === 'all') {
							const statusHeader = document.createElement('div');
							statusHeader.className = 'sub-task-status-header';
							statusHeader.innerHTML = `
								<div class="status-indicator" style="background: ${getStatusColor(status)}"></div>
								<span class="status-label">${statusLabels[status]}</span>
								<span class="status-count">(${groupedTasks[status].length})</span>
							`;
							container.appendChild(statusHeader);
						}
						
						// Create tasks for this status
						groupedTasks[status].forEach(task => {
							const item = document.createElement('div');
							item.className = 'sub-task-item';
							if (status === 'completed') {
								item.classList.add('completed');
							}
							item.innerHTML = `
								<div class="sub-task-status" style="background: ${getStatusColor(task.task_status)}"></div>
								<div class="sub-task-content">
									<div class="sub-task-title">${task.task_title}</div>
									<div class="sub-task-meta">
										<span>#${task.unique_id}</span>
										<span class="priority-${task.priority_level || 'low'}">${task.priority_level || 'Low'}</span>
										<span>${task.estimation_time || '0'}h</span>
									</div>
								</div>
							`;
							item.onclick = () => {
								closeTaskModal();
								openTaskModal(task.unique_id);
							};
							container.appendChild(item);
						});
					}
				});
				
				if (filteredTasks.length === 0) {
					container.innerHTML = '<div class="no-sub-tasks">No sub-tasks found for selected status</div>';
				}
			}
			
			function filterSubTasks(status) {
				renderSubTasks(allSubTasks, status);
			}

			function getStatusColor(status) {
				const colors = {
					'todo': '#ffbe0b',
					'in_progress': '#3a86ff',
					'in_review': '#17a2b8',
					'completed': '#06d6a0',
					'done': '#06d6a0',
					'hold': '#fd7e14',
					'canceled': '#dc3545',
					'backlog': '#adb5bd'
				};
				return colors[status] || '#6c757d';
			}

			// Enhanced loadComments function
			function loadComments(taskId) {
				return fetch(`<?= base_url('tracker/get_comments/') ?>${taskId}`, {
					headers: {
						'X-Requested-With': 'XMLHttpRequest'
					}
				})
				.then(response => {
					if (!response.ok) throw new Error('Failed to load comments');
					return response.json();
				})
				.then(comments => {
					const commentsList = document.getElementById('commentsList');
					commentsList.innerHTML = '';
					
					if (!comments || comments.length === 0) {
						commentsList.innerHTML = '<div class="no-comments"><?php echo $this->lang->line("no_comments_yet"); ?></div>';
						return;
					}
					
					comments.forEach(comment => {
						const commentDiv = document.createElement('div');
						commentDiv.className = 'comment-item';
						const currentUserId = <?= get_loggedin_user_id() ?>;
						const canEdit = parseInt(comment.author_id) === currentUserId;
						
						commentDiv.innerHTML = `
							<img class="rounded" src="${comment.author_photo}" width="40" height="40" alt="${comment.author_name}">
							<div class="comment-content">
								<div>
								  <span class="comment-author" style="font-weight: bold; margin-right: 10px;">
									${comment.author_name}
								  </span>
								  <span class="comment-meta" style="color: #888; font-size: 0.9em;">
									${comment.formatted_date || formatCommentDate(comment.created_at)}
								  </span>
								  ${canEdit ? `<div class="comment-actions" style="float: right;">
									<button onclick="editComment(${comment.id}, \`${comment.comment_text.replace(/`/g, '\\`')}\`)" class="btn btn-sm btn-link" title="Edit">
									  <i class="fas fa-edit"></i>
									</button>
									<button onclick="deleteComment(${comment.id})" class="btn btn-sm btn-link text-danger" title="Delete">
									  <i class="fas fa-trash"></i>
									</button>
								  </div>` : ''}
								</div>
								<div class="comment-text" id="comment-text-${comment.id}">${processMentions(comment.comment_text.replace(/\n/g, '<br>'))}</div>
								<div class="comment-edit-form" id="comment-edit-${comment.id}" style="display: none;">
								  <div class="mention-container" style="position: relative;">
									<textarea class="form-control" id="edit-textarea-${comment.id}" placeholder="Edit comment... (Use @ to mention users)"></textarea>
									<div id="edit-mention-dropdown-${comment.id}" class="mention-dropdown" style="display: none;"></div>
								  </div>
								  <div class="mt-2">
									<button onclick="saveComment(${comment.id})" class="btn btn-sm btn-success">Save</button>
									<button onclick="cancelEdit(${comment.id})" class="btn btn-sm btn-secondary">Cancel</button>
								  </div>
								</div>
							</div>
						`;
						commentsList.appendChild(commentDiv);
					});
				})
				.catch(err => {
					console.error('Error loading comments:', err);
					document.getElementById('commentsList').innerHTML = 
						'<div class="no-comments">Error loading comments</div>';
				});
			}
			</script>
			
			<script>
		// Enhanced postComment function
		function postComment(taskId, commentText) {
			return new Promise((resolve, reject) => {
				const formData = new FormData();
				formData.append('task_id', taskId);
				formData.append('comment_text', commentText);
				formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');

				fetch(`<?= base_url('tracker/add_comment') ?>`, {
					method: 'POST',
					body: formData,
					headers: {
						'X-Requested-With': 'XMLHttpRequest'
					}
				})
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}
					return response.json();
				})
				.then(data => {
					if (data.success) {
						resolve(data);
					} else {
						reject(new Error(data.message || 'Failed to post comment'));
					}
				})
				.catch(error => {
					console.error('Error posting comment:', error);
					reject(error);
				});
			});
		}

		// Mention system functionality
		let mentionUsers = [];
		let selectedMentionIndex = -1;
		let mentionStartPos = -1;
		let mentionQuery = '';

		// Initialize mention system
		function initializeMentionSystem() {
			const textarea = document.getElementById('newCommentText');
			const dropdown = document.getElementById('mentionDropdown');

			// Remove existing listeners to prevent duplicates
			textarea.removeEventListener('input', handleMentionInput);
			textarea.removeEventListener('keydown', handleMentionKeydown);
			document.removeEventListener('click', closeMentionDropdown);
			
			// Add fresh listeners
			textarea.addEventListener('input', handleMentionInput);
			textarea.addEventListener('keydown', handleMentionKeydown);
			document.addEventListener('click', closeMentionDropdown);
			
			// Reset mention state
			mentionUsers = [];
			selectedMentionIndex = -1;
			mentionStartPos = -1;
			mentionQuery = '';
		}

		function handleMentionInput(e) {
			const textarea = e.target;
			const text = textarea.value;
			const cursorPos = textarea.selectionStart;

			// Find @ symbol before cursor
			let atPos = -1;
			for (let i = cursorPos - 1; i >= 0; i--) {
				if (text[i] === '@') {
					atPos = i;
					break;
				} else if (text[i] === ' ' || text[i] === '\n') {
					break;
				}
			}

			if (atPos !== -1) {
				mentionStartPos = atPos;
				mentionQuery = text.substring(atPos + 1, cursorPos);
				// Add a small delay to prevent rapid requests
				clearTimeout(window.mentionTimeout);
				window.mentionTimeout = setTimeout(() => {
					showMentionDropdown(mentionQuery);
				}, 300);
			} else {
				hideMentionDropdown();
				clearTimeout(window.mentionTimeout);
			}
		}

		function handleMentionKeydown(e) {
			const dropdown = document.getElementById('mentionDropdown');
			if (dropdown.style.display === 'none') return;

			switch (e.key) {
				case 'ArrowDown':
					e.preventDefault();
					selectedMentionIndex = Math.min(selectedMentionIndex + 1, mentionUsers.length - 1);
					updateMentionSelection();
					break;
				case 'ArrowUp':
					e.preventDefault();
					selectedMentionIndex = Math.max(selectedMentionIndex - 1, 0);
					updateMentionSelection();
					break;
				case 'Enter':
					e.preventDefault();
					if (selectedMentionIndex >= 0) {
						selectMention(mentionUsers[selectedMentionIndex]);
					}
					break;
				case 'Escape':
					hideMentionDropdown();
					break;
			}
		}

		function showMentionDropdown(query) {
			// Add CSRF token to the request
			const formData = new FormData();
			formData.append('search', query);
			formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');
			
			fetch(`<?= base_url('tracker/get_mention_users') ?>`, {
				method: 'POST',
				body: formData,
				headers: {'X-Requested-With': 'XMLHttpRequest'}
			})
			.then(response => {
				if (!response.ok) {
					throw new Error('Network response was not ok');
				}
				return response.json();
			})
			.then(users => {
				mentionUsers = users || [];
				selectedMentionIndex = users && users.length > 0 ? 0 : -1;
				renderMentionDropdown(users || []);
			})
			.catch(error => {
				console.error('Error fetching users:', error);
				hideMentionDropdown();
			});
		}

		function renderMentionDropdown(users) {
			const dropdown = document.getElementById('mentionDropdown');
			if (users.length === 0) {
				dropdown.style.display = 'none';
				return;
			}

			dropdown.innerHTML = users.map((user, index) => `
				<div class="mention-item ${index === selectedMentionIndex ? 'selected' : ''}" 
					 onclick="selectMention(${JSON.stringify(user).replace(/"/g, '&quot;')})">
					${user.photo ? `<img src="${user.photo}" class="mention-avatar" alt="${user.name}">` : '<div class="mention-avatar" style="background: #ddd;"></div>'}
					<span class="mention-name">${user.name}</span>
				</div>
			`).join('');
			dropdown.style.display = 'block';
		}

		function updateMentionSelection() {
			const items = document.querySelectorAll('.mention-item');
			items.forEach((item, index) => {
				item.classList.toggle('selected', index === selectedMentionIndex);
			});
		}

		function selectMention(user) {
			const textarea = document.getElementById('newCommentText');
			const text = textarea.value;
			const beforeMention = text.substring(0, mentionStartPos);
			const afterMention = text.substring(textarea.selectionStart);
			const mentionText = `@[${user.id}]${user.name}`;

			textarea.value = beforeMention + mentionText + afterMention;
			const newCursorPos = beforeMention.length + mentionText.length;
			textarea.setSelectionRange(newCursorPos, newCursorPos);
			textarea.focus();

			hideMentionDropdown();
		}

		function hideMentionDropdown() {
			const dropdown = document.getElementById('mentionDropdown');
			if (dropdown) {
				dropdown.style.display = 'none';
				dropdown.innerHTML = '';
			}
			selectedMentionIndex = -1;
			mentionStartPos = -1;
			mentionQuery = '';
			mentionUsers = [];
			clearTimeout(window.mentionTimeout);
		}

		function closeMentionDropdown(e) {
			if (!e.target.closest('.mention-container')) {
				hideMentionDropdown();
			}
		}

		// Edit comment mention system
		let editMentionUsers = [];
		let editSelectedMentionIndex = -1;
		let editMentionStartPos = -1;
		
		function setupEditMentionSystem(commentId) {
			const textarea = document.getElementById(`edit-textarea-${commentId}`);
			const dropdown = document.getElementById(`edit-mention-dropdown-${commentId}`);
			
			textarea.addEventListener('input', function(e) {
				const text = e.target.value;
				const cursorPos = e.target.selectionStart;
				
				let atPos = -1;
				for (let i = cursorPos - 1; i >= 0; i--) {
					if (text[i] === '@') {
						atPos = i;
						break;
					} else if (text[i] === ' ' || text[i] === '\n') {
						break;
					}
				}
				
				if (atPos !== -1) {
					editMentionStartPos = atPos;
					const query = text.substring(atPos + 1, cursorPos);
					showEditMentionDropdown(query, commentId);
				} else {
					dropdown.style.display = 'none';
				}
			});
			
			textarea.addEventListener('keydown', function(e) {
				if (dropdown.style.display === 'none') return;
				
				switch (e.key) {
					case 'ArrowDown':
						e.preventDefault();
						editSelectedMentionIndex = Math.min(editSelectedMentionIndex + 1, editMentionUsers.length - 1);
						updateEditMentionSelection(commentId);
						break;
					case 'ArrowUp':
						e.preventDefault();
						editSelectedMentionIndex = Math.max(editSelectedMentionIndex - 1, 0);
						updateEditMentionSelection(commentId);
						break;
					case 'Enter':
						e.preventDefault();
						if (editSelectedMentionIndex >= 0) {
							selectEditMention(editMentionUsers[editSelectedMentionIndex], commentId);
						}
						break;
					case 'Escape':
						dropdown.style.display = 'none';
						break;
				}
			});
			
			
			function showEditMentionDropdown(query, commentId) {
				// Add CSRF token to the request
				const formData = new FormData();
				formData.append('search', query);
				formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');
				
				fetch(`<?= base_url('tracker/get_mention_users') ?>`, {
					method: 'POST',
					body: formData,
					headers: {'X-Requested-With': 'XMLHttpRequest'}
				})
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}
					return response.json();
				})
				.then(users => {
					editMentionUsers = users || [];
					editSelectedMentionIndex = users && users.length > 0 ? 0 : -1;
					const dropdown = document.getElementById(`edit-mention-dropdown-${commentId}`);
					if (!users || users.length === 0) {
						dropdown.style.display = 'none';
						return;
					}
					dropdown.innerHTML = users.map((user, index) => `
						<div class="mention-item ${index === editSelectedMentionIndex ? 'selected' : ''}" 
							 onclick="selectEditMention(${JSON.stringify(user).replace(/"/g, '&quot;')}, ${commentId})">
							${user.photo ? `<img src="${user.photo}" class="mention-avatar" alt="${user.name}">` : '<div class="mention-avatar" style="background: #ddd;"></div>'}
							<span class="mention-name">${user.name}</span>
						</div>
					`).join('');
					dropdown.style.display = 'block';
				})
				.catch(error => {
					console.error('Error fetching users:', error);
					const dropdown = document.getElementById(`edit-mention-dropdown-${commentId}`);
					if (dropdown) dropdown.style.display = 'none';
				});
			}
		}
		
		function selectEditMention(user, commentId) {
			const textarea = document.getElementById(`edit-textarea-${commentId}`);
			const text = textarea.value;
			const beforeMention = text.substring(0, editMentionStartPos);
			const afterMention = text.substring(textarea.selectionStart);
			const mentionText = `@[${user.id}]${user.name}`;
			
			textarea.value = beforeMention + mentionText + afterMention;
			const newCursorPos = beforeMention.length + mentionText.length;
			textarea.setSelectionRange(newCursorPos, newCursorPos);
			textarea.focus();
			
			document.getElementById(`edit-mention-dropdown-${commentId}`).style.display = 'none';
		}
		
		function updateEditMentionSelection(commentId) {
			const items = document.querySelectorAll(`#edit-mention-dropdown-${commentId} .mention-item`);
			items.forEach((item, index) => {
				item.classList.toggle('selected', index === editSelectedMentionIndex);
			});
		}

		// Process mentions in comment text for display
		function processMentions(text) {
			return text.replace(/@\[(\d+)\]([^@\s]+)/g, '<span class="mentioned-user">@$2</span>');
		}

		// Update the event listener
			document.getElementById('submitCommentBtn').addEventListener('click', function() {
			const taskId = document.getElementById('modalTaskId').textContent;
			const commentText = document.getElementById('newCommentText').value.trim();
			
			if (!commentText) {
				showToast('Please enter a comment', 'error');
				return;
			}
			
			const btn = this;
			btn.disabled = true;
			btn.textContent = 'Posting...';
			
			postComment(taskId, commentText)
				.then(response => {
					document.getElementById('newCommentText').value = '';
					return loadComments(taskId);
				})
				.then(() => {
					showToast('Comment posted successfully');
				})
				.catch(error => {
					console.error('Error:', error);
					showToast(error.message || 'Failed to post comment', 'error');
				})
				.finally(() => {
					btn.disabled = false;
					btn.textContent = 'Post Comment';
				});
		});
		
		// Comment edit/delete functions
		function editComment(commentId, currentText) {
			document.getElementById(`comment-text-${commentId}`).style.display = 'none';
			document.getElementById(`comment-edit-${commentId}`).style.display = 'block';
			const textarea = document.getElementById(`edit-textarea-${commentId}`);
			textarea.value = currentText || '';
			setupEditMentionSystem(commentId);
			textarea.focus();
		}

		function cancelEdit(commentId) {
			document.getElementById(`comment-text-${commentId}`).style.display = 'block';
			document.getElementById(`comment-edit-${commentId}`).style.display = 'none';
		}

		function saveComment(commentId) {
			const newText = document.getElementById(`edit-textarea-${commentId}`).value.trim();
			if (!newText) {
				showToast('Comment cannot be empty', 'error');
				return;
			}

			const formData = new FormData();
			formData.append('comment_id', commentId);
			formData.append('comment_text', newText);
			formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');

			fetch(`<?= base_url('tracker/update_comment') ?>`, {
				method: 'POST',
				body: formData,
				headers: {'X-Requested-With': 'XMLHttpRequest'}
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					document.getElementById(`comment-text-${commentId}`).innerHTML = processMentions(newText.replace(/\n/g, '<br>'));
					cancelEdit(commentId);
					showToast('Comment updated successfully');
				} else {
					showToast(data.message || 'Failed to update comment', 'error');
				}
			})
			.catch(error => {
				console.error('Error:', error);
				showToast('Failed to update comment', 'error');
			});
		}

		function deleteComment(commentId) {
			if (!confirm('Are you sure you want to delete this comment?')) {
				return;
			}

			const formData = new FormData();
			formData.append('comment_id', commentId);
			formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');

			fetch(`<?= base_url('tracker/delete_comment') ?>`, {
				method: 'POST',
				body: formData,
				headers: {'X-Requested-With': 'XMLHttpRequest'}
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					const taskId = document.getElementById('modalTaskId').textContent;
					loadComments(taskId);
					showToast('Comment deleted successfully');
				} else {
					showToast(data.message || 'Failed to delete comment', 'error');
				}
			})
			.catch(error => {
				console.error('Error:', error);
				showToast('Failed to delete comment', 'error');
			});
		}

		function showToast(message, type = 'success') {
			// Simple toast notification - you can replace with your preferred notification system
			const toast = document.createElement('div');
			toast.className = `alert alert-${type === 'error' ? 'danger' : 'success'} toast-notification`;
			toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
			toast.textContent = message;
			document.body.appendChild(toast);
			setTimeout(() => toast.remove(), 3000);
		}

			function resetModalToLoading() {
				document.getElementById('modalTaskId').textContent = 'Loading...';
				document.getElementById('modalTaskTitle').textContent = 'Loading...';
				document.getElementById('modalTaskDescription').textContent = 'Loading...';
				document.getElementById('modalCreatedBy').textContent = 'Loading...';
				// Reset select dropdowns
				document.getElementById('modalAssigneeSelect').value = '';
				document.getElementById('modalCoordinatorSelect').value = '';
				
				// Clear labels
				const labelsContainer = document.getElementById('modalLabelsContainer');
			}

			function showModalError(message) {
				document.getElementById('modalTaskTitle').textContent = 'Error';
				document.getElementById('modalTaskDescription').textContent = message;
			}


			function populateModal(task, taskId) {
				console.log('Populating modal with task:', task);
				document.getElementById('modalTaskId').textContent = task.unique_id || 'N/A';
				// Store the numeric ID as well for updates
				document.getElementById('modalTaskId').dataset.numericId = task.id;
				document.getElementById('modalTaskTitle').textContent = task.task_title || 'No Title';
				document.getElementById('modalTaskDescription').innerHTML = task.task_description || '<em>No description</em>';
				
				// Handle parent task display
				const parentInfo = document.getElementById('parentTaskInfo');
				if (task.parent_issue_unique_id && task.parent_task_title) {
					document.getElementById('parentTaskDetails').textContent = task.parent_issue_unique_id + ' - ' + task.parent_task_title;
					parentInfo.style.display = 'block';
					parentInfo.dataset.parentId = task.parent_issue_id;
				} else {
					parentInfo.style.display = 'none';
				}

				populateSelect('modalStatusSelect', statusConfig, task.task_status);
				populateSelect('modalPrioritySelect', priorityConfig, task.priority_level);
				// Category
				document.getElementById('modalCategorySelect').value = task.category || 'Milestone';
				// Users - Set the select values
				document.getElementById('modalAssigneeSelect').value = task.assigned_to || '';
				document.getElementById('modalCoordinatorSelect').value = task.coordinator || '';
				// Keep the created by as read-only display
				document.getElementById('modalCreatedBy').textContent = task.created_by_name || 'Unknown';
				
				// Set user avatars for created by only
				const createdByAvatar = document.getElementById('modalCreatedByAvatar');
				
				if (task.created_by_photo) {
					createdByAvatar.src = task.created_by_photo;
					createdByAvatar.style.display = 'block';
				} else {
					createdByAvatar.style.display = 'none';
				}
				// Update title and description
				const titleElement = document.getElementById('modalTaskTitle');
				titleElement.textContent = task.task_title || '';
				titleElement.dataset.originalValue = task.task_title || '';
				  
				const descElement = document.getElementById('modalTaskDescription');
				descElement.innerHTML = task.task_description || '';
				descElement.dataset.originalValue = task.task_description || ''; 
  
				document.getElementById('modalComponentSelect').value = task.component || '';
				document.getElementById('modalMilestoneSelect').value = task.milestone || '';
				document.getElementById('modalTaskTypeSelect').value = task.task_type || '';
				document.getElementById('modalDueDateInput').value = task.estimated_end_time ? task.estimated_end_time.split(' ')[0] : '';
				document.getElementById('modalEstimationInput').value = task.estimation_time || '';
				document.getElementById('modalSpentTime').textContent = (task.spent_time || 0) + 'h';
				document.getElementById('modalRemainingTime').textContent = (task.remaining_time || 0) + 'h';
				
				// Load parent task options and set current parent
				loadParentTaskOptions(task.id, task.parent_issue);
				
				// Set up add sub issue button
				document.getElementById('addSubIssueBtn').onclick = () => openSubIssueModal(task.id);
				// Handle labels
				const labelsSelect = document.getElementById('modalLabelsSelect');
				if (task.label) {
					const labelIds = task.label.split(',').map(id => id.trim());
					$(labelsSelect).val(labelIds).trigger('change');
				}
				
				// Handle SOP IDs
				const sopSelect = document.getElementById('modalSopIdsSelect');
				if (task.sop_ids) {
					const sopIds = task.sop_ids.split(',').map(id => id.trim());
					$(sopSelect).val(sopIds).trigger('change');
				}
				
				// Show accepted details if task is approved
				const acceptedSection = document.getElementById('acceptedDetailsSection');
				if (task.approved_at && task.approved_by_name) {
					document.getElementById('acceptedBy').innerHTML = '<strong>Accepted by:</strong> ' + task.approved_by_name;
					document.getElementById('acceptedAt').innerHTML = '<strong>Accepted on:</strong> ' + formatDateTime(task.approved_at);
					acceptedSection.style.display = 'block';
				} else {
					acceptedSection.style.display = 'none';
				}
				
				// Handle customer query data
				const customerQuerySection = document.getElementById('customerQuerySection');
				if (task.category === 'Customer Query' && (task.customer_query_data || task.source || task.contact_info || task.requested_at)) {
					let queryData = {};
					
					// Try to parse customer_query_data if it exists
					if (task.customer_query_data && task.customer_query_data !== null) {
						try {
							queryData = typeof task.customer_query_data === 'string' ? 
								JSON.parse(task.customer_query_data) : task.customer_query_data;
						} catch (e) {
							console.warn('Failed to parse customer_query_data:', e);
							queryData = {};
						}
					}
					
					// Fallback to individual fields if customer_query_data is not available
					if (!queryData.source && task.source) queryData.source = task.source;
					if (!queryData.contact_info && task.contact_info) queryData.contact_info = task.contact_info;
					if (!queryData.requested_at && task.requested_at) queryData.requested_at = task.requested_at;
					if (!queryData.request_body && task.request_body) queryData.request_body = task.request_body;
					
					document.getElementById('modalQuerySource').textContent = queryData.source || 'N/A';
					document.getElementById('modalQueryContact').textContent = queryData.contact_info || 'N/A';
					document.getElementById('modalQueryRequestedAt').textContent = queryData.requested_at || 'N/A';
					
					if (queryData.request_body && queryData.request_body.trim()) {
						document.getElementById('modalQueryBody').textContent = queryData.request_body;
						document.getElementById('modalQueryBodySection').style.display = 'block';
					} else {
						document.getElementById('modalQueryBodySection').style.display = 'none';
					}
					
					customerQuerySection.style.display = 'block';
				} else {
					customerQuerySection.style.display = 'none';
				}
				
			}
			
			// Add a function to handle escape key and cancel edits
			function setupEditableFields() {
			  const editables = document.querySelectorAll('[contenteditable="true"]');
			  
			  editables.forEach(el => {
				el.addEventListener('keydown', function(e) {
				  if (e.key === 'Escape') {
					this.textContent = this.dataset.originalValue;
					this.blur();
				  }
				});
				
				el.addEventListener('focus', function() {
				  this.dataset.originalValue = this.textContent;
				});
			  });
			}

			function populateSelect(id, options, selectedValue) {
				const select = document.getElementById(id);
				select.innerHTML = '';
				for (const key in options) {
					if (id === 'modalStatusSelect' && key === 'completed' && selectedValue !== 'completed') continue;
					const opt = document.createElement('option');
					opt.value = key;
					opt.textContent = options[key].title || options[key].name || options[key];
					if (key == selectedValue) opt.selected = true;
					select.appendChild(opt);
				}
			}

			function populateMultiSelect(id, options, selectedArray) {
				const select = document.getElementById(id);
				select.innerHTML = '';
				const values = Array.isArray(selectedArray) ? selectedArray : JSON.parse(selectedArray || '[]');
				for (const key in options) {
					const opt = document.createElement('option');
					opt.value = key;
					opt.textContent = options[key].name || options[key].label_name;
					if (values.includes(key)) opt.selected = true;
					select.appendChild(opt);
				}
			}

			function updateTaskField(field, value) {
				const taskElement = document.getElementById('modalTaskId');
				const taskId = taskElement.dataset.numericId || taskElement.textContent;
				
				// Debug logging
				console.log('Updating field:', field, 'with value:', value, 'for task:', taskId, 'using numeric ID:', taskElement.dataset.numericId);
				
				// Create form data instead of JSON
				const formData = new FormData();
				formData.append('task_id', taskId);
				formData.append('field', field);
				// For description, we need to preserve HTML
				if (field === 'task_description') {
					formData.append('value', value);
				} else {
					formData.append('value', typeof value === 'string' ? value.trim() : value);
				}
  
				// Add CSRF token
				const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
				const csrfHash = '<?= $this->security->get_csrf_hash() ?>';
				formData.append(csrfName, csrfHash);
				console.log('CSRF token added:', csrfName, csrfHash);

				fetch(`<?= base_url('tracker/update_task_field') ?>`, {
					method: 'POST',
					body: formData,
					headers: {
						'X-Requested-With': 'XMLHttpRequest' // Important for CodeIgniter to recognize AJAX
					}
				})
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}
					return response.json();
				})
				.then(res => {
					console.log('Update response:', res);
					if (res.success) {
						console.log('Update successful for field:', field);
						 // Update the original value to prevent reverting on next edit
						  if (field === 'task_title') {
							document.getElementById('modalTaskTitle').dataset.originalValue = value;
						  } else if (field === 'task_description') {
							document.getElementById('modalTaskDescription').dataset.originalValue = value;
						  }
						// If status or category was updated, refresh the task list
						else if (field === 'task_status' || field === 'category') {
							 location.reload();
						}
						// If parent_issue was updated, update the parent task display
						else if (field === 'parent_issue') {
							const parentInfo = document.getElementById('parentTaskInfo');
							if (res.parent_details) {
								document.getElementById('parentTaskDetails').textContent = res.parent_details.unique_id + ' - ' + res.parent_details.task_title;
								parentInfo.style.display = 'block';
								parentInfo.dataset.parentId = value;
							}
						}
					} else {
						console.error('Update failed:', res.message);
						showToast(res.message || 'Update failed', 'error');
						revertSelectValue(field);
					}
				})
				.catch(error => {
					console.error('Network error:', error);
					// showToast('Network error - please try again', 'error');
					revertSelectValue(field);
				});
			}
			function revertFieldValue(field) {
			  if (field === 'task_title') {
				const el = document.getElementById('modalTaskTitle');
				el.textContent = el.dataset.originalValue;
			  } else if (field === 'task_description') {
				const el = document.getElementById('modalTaskDescription');
				el.innerHTML = el.dataset.originalValue;
			  } else {
				revertSelectValue(field);
			  }
			}
			function revertSelectValue(field) {
				// Revert to the previous value in the select
				const select = document.getElementById(`modal${field.charAt(0).toUpperCase() + field.slice(1)}Select`);
				if (select) {
					select.value = select.dataset.prevValue || '';
				}
			}

			// Load parent task options function
			function loadParentTaskOptions(currentTaskId, selectedParentId) {
				fetch(`<?= base_url('tracker/get_all_issues/') ?>${currentTaskId}`, {
					headers: {'X-Requested-With': 'XMLHttpRequest'}
				})
				.then(response => response.json())
				.then(tasks => {
					const select = $('#modalParentTaskSelect');
					select.empty().append('<option value=""><?= translate('no_parent') ?></option>');
					
					tasks.forEach(task => {
						if (task.id != currentTaskId) {
							select.append(new Option(`${task.unique_id} - ${task.task_title}`, task.id));
						}
					});
					
					// Initialize or reinitialize Select2
					select.select2({
						placeholder: 'Search and select parent task...',
						allowClear: true,
						width: '100%'
					});
					
					// Set selected value
					if (selectedParentId) {
						select.val(selectedParentId).trigger('change');
					}
				})
				.catch(err => console.error('Error loading parent tasks:', err));
			}
			
			// Add event listeners for status, priority, category, assignee, coordinator, and other changes
			document.querySelectorAll('#modalStatusSelect, #modalPrioritySelect, #modalCategorySelect, #modalParentTaskSelect, #modalTaskTypeSelect, #modalAssigneeSelect, #modalCoordinatorSelect').forEach(select => {
				select.addEventListener('focus', function() {
					this.dataset.prevValue = this.value;
				});
				select.addEventListener('change', function() {
					const originalValue = this.value;
					this.disabled = true;
					const field = this.id.replace('modal', '').replace('Select', '').toLowerCase();
					const fieldMap = {'status': 'task_status', 'priority': 'priority_level', 'category': 'category', 'assignee': 'assigned_to', 'coordinator': 'coordinator'};
					const actualField = fieldMap[field] || field;
					updateTaskField(actualField, originalValue)
						.finally(() => {
							this.disabled = false;
						});
				});
			});

			function getSelectedLabels() {
				return Array.from(document.getElementById('modalLabelsSelect').selectedOptions).map(o => o.value);
			}
			
			function deleteIssue() {
				if (!confirm('Are you sure you want to delete this issue?')) {
					return;
				}
				const taskId = document.getElementById('modalTaskId').textContent;
				const formData = new FormData();
				formData.append('task_id', taskId);
				formData.append('<?= $this->security->get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');
				fetch(`<?= base_url('tracker/delete_issue') ?>`, {
					method: 'POST',
					body: formData,
					headers: {'X-Requested-With': 'XMLHttpRequest'}
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						closeTaskModal();
						location.reload();
					} else {
						showToast(data.message || 'Failed to delete issue', 'error');
					}
				})
				.catch(error => {
					showToast('Failed to delete issue', 'error');
				});
			}


			function openParentTask() {
				const parentInfo = document.getElementById('parentTaskInfo');
				const parentId = parentInfo.dataset.parentId;
				if (parentId) {
					closeTaskModal();
					openTaskModal(parentId);
				}
			}
			
			function closeTaskModal() {
				document.getElementById('taskModal').style.display = 'none';
				document.body.style.overflow = 'auto';
			}

			document.getElementById('taskModal').addEventListener('click', function(e) {
				if (e.target === this) closeTaskModal();
			});
			</script>
			
			
			<script>
					document.addEventListener('keydown', function(e) {
						if (e.key === 'Escape') closeTaskModal();
					});
					  function closeTaskModal() {
						document.getElementById('taskModal').style.display = 'none';
						document.body.style.overflow = 'auto';
					}

					// Close modal when clicking outside
					document.getElementById('taskModal').addEventListener('click', function(e) {
						if (e.target === this) {
							closeTaskModal();
						}
					});

					// Close modal on Escape key
					document.addEventListener('keydown', function(e) {
						if (e.key === 'Escape') {
							closeTaskModal();
						}
					});

					function toggleAccordion(id) {
						const header = document.querySelector(`[onclick="toggleAccordion('${id}')"]`);
						const body = document.getElementById('accordion-body-' + id);
						
						header.classList.toggle('active');
						body.classList.toggle('active');
						
						const allHeaders = document.querySelectorAll('.accordion-header');
						const allBodies = document.querySelectorAll('.accordion-body');
						
						// Add this to your JavaScript initialization
						document.querySelectorAll('#modalStatusSelect, #modalPrioritySelect').forEach(select => {
							select.addEventListener('change', function() {
								// Show loading indicator
								const originalValue = this.value;
								this.disabled = true;
								
								// Get the field name from the select ID
								const field = this.id.replace('modal', '').replace('Select', '').toLowerCase();
								const fieldMap = {'status': 'task_status', 'priority': 'priority_level'};
								const actualField = fieldMap[field] || field;
								
								updateTaskField(actualField, originalValue)
									.finally(() => {
										this.disabled = false;
									});
							});
						});
						allHeaders.forEach(h => {
							if (h !== header) {
								h.classList.remove('active');
							}
						});
						
						allBodies.forEach(b => {
							if (b !== body) {
								b.classList.remove('active');
							}
						}); 
					}

					// Initialize
					document.addEventListener('DOMContentLoaded', function() {
						loadIssues();
						initializeSearch();
						initializeMentionSystem();
						
						// Initialize Select2 for milestone filter
						$('#milestoneFilter').select2({
							allowClear: true,
							placeholder: '🏁 Search Milestones...',
							width: '100%'
						});
						
						// Load All button event listener
						document.getElementById('loadAllBtn').addEventListener('click', function() {
							this.disabled = true;
							this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
							loadIssues(true);
						});
					});

					// Load issues via AJAX
					let currentLoadAll = false;
					
					function loadIssues(loadAll = false) {
						currentLoadAll = loadAll;
						
						document.getElementById('loadingState').style.display = 'block';
						document.getElementById('issuesContainer').style.display = 'none';
						
						const url = `<?= base_url('tracker/get_my_coordination_data') ?>${loadAll ? '?load_all=true' : ''}`;
						
						fetch(url, {
							headers: {'X-Requested-With': 'XMLHttpRequest'}
						})
						.then(response => {
							if (!response.ok) throw new Error('Network response was not ok');
							return response.json();
						})
						.then(data => {
							if (data.success) {
								renderIssues(data.issues, data.status_config);
								updateDataInfo(data.load_all, data.total_count);
								document.getElementById('loadingState').style.display = 'none';
								document.getElementById('issuesContainer').style.display = 'block';
							} else {
								showError(data.message || 'Failed to load issues');
							}
						})
						.catch(error => {
							console.error('Error loading issues:', error);
							showError('Failed to load issues. Please refresh the page.');
						});
					}
					
					function updateDataInfo(loadAll, totalCount) {
						const loadAllBtn = document.getElementById('loadAllBtn');
						const dataInfo = document.getElementById('dataInfo');
						
						if (loadAll) {
							loadAllBtn.style.display = 'none';
							dataInfo.textContent = `Showing all ${totalCount} issues`;
						} else {
							loadAllBtn.style.display = 'inline-block';
							dataInfo.textContent = `Showing last 30 days (${totalCount} issues)`;
						}
					}

					// Render issues function
					function renderIssues(groupedIssues, statusConfig) {
						const container = document.getElementById('issuesContainer');
						container.innerHTML = '';

						// Use the order from statusConfig (already ordered by server)
						Object.keys(statusConfig).forEach(status => {
							const issues = groupedIssues[status] || [];
							const config = statusConfig[status];
							const uniqueId = 'status_' + status;

							const accordionCard = document.createElement('div');
							accordionCard.className = 'accordion-card';
							accordionCard.innerHTML = `
								<div class="accordion-header" onclick="toggleAccordion('${uniqueId}')">
									<div class="col-md-8 status-info">
										<div class="status-icon" style="background: linear-gradient(135deg, ${config.color} 0%, ${config.color}dd 100%);">
											<i class="${config.icon}"></i>
										</div>
										<span>${config.title}</span>
									</div>
									<div class="item-count">${issues.length} items</div>
								</div>
								<div class="accordion-body" id="accordion-body-${uniqueId}">
									${renderTaskItems(issues, config)}
								</div>
							`;
							container.appendChild(accordionCard);
						});
					}

					// Render task items function
					function renderTaskItems(issues, statusConfig) {
						if (!issues || issues.length === 0) {
							return `
								<div class="no-data">
									<i class="${statusConfig.icon}"></i>
									<div>No tasks in ${statusConfig.title.toLowerCase()}</div>
								</div>
							`;
						}

						return issues.map(task => {
						const isMainTask = !task.parent_issue || task.parent_issue === null || task.parent_issue === '';
						return `
							<div class="task-item ${isMainTask ? 'main-task' : 'sub-task'}" onclick="openTaskModal(${task.id})" style="cursor: pointer; ${isMainTask ? 'font-weight: 700;' : ''}" data-milestone-id="${task.milestone || ''}">
								<div class="col-md-10" style="display: flex; align-items: center; ">
									<div class="task-id" style="font-weight: ${isMainTask ? '700' : '600'}; font-size: ${isMainTask ? '1.4rem' : '1.2rem'}; font-style: ${isMainTask ? 'italic' : ''};  min-width: 80px; ${isMainTask ? 'text-shadow: 0 1px 2px rgba(0,0,0,0.1);' : ''}">${task.unique_id}</div>
									<div class="task-title" style="font-weight: ${isMainTask ? '700' : '600'}; font-size: ${isMainTask ? '1.4rem' : '1.2rem'};font-style: ${isMainTask ? 'italic' : ''};  flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; ${isMainTask ? 'text-shadow: 0 1px 2px rgba(0,0,0,0.1);' : ''}">${task.task_title}</div>
								</div>
								<!-- <div class="task-labels" title="Labels">
									${task.label_names ? `L - ${task.label_names}` : '-'}
								</div>
								<div class="task-component" title="Initiatives">
									${task.component_title ? `I-${task.component_title}` : ''}
								</div>
								<div class="task-milestone" title="Milestone">
									${task.milestone_title ? `M-${task.milestone_title}` : ''}
								</div> 
								<div class="task-type" title="Task Type">
									${task.task_type_name ? `T-${task.task_type_name}` : ''}
								</div> -->
								<div class="col-md-2" style="display: flex; justify-content: flex-end; align-items: center; gap: 8px;">
								
									<div class="task-category" style="padding: 5px 5px; border: 1px solid #cccccc; background: #f2f2f4; color: #000; border-radius: 20px; font-size: 1.2rem; text-align: center; min-width: 90px;" title="Category">
										${task.category || 'N/A'}
									</div>
									<div class="task-milestone" title="Milestone" style="display:none;">
										${task.milestone_title ? `M-${task.milestone_title}` : ''}
									</div>
									${task.parent_issue ? 
										`<div class="parent-task-id" style="padding: 5px 10px; border: 1px solid #007bff; background: #e7f3ff; color: #007bff; border-radius: 12px; font-size: 1rem; cursor: pointer; font-weight: 500; min-width: 70px; text-align:center;" 
											title="Parent Task: ${task.parent_unique_id}" 
											onclick="openTaskModal(${task.parent_issue}); event.stopPropagation();">
											${task.parent_unique_id}
										</div>` : ''
									}
									<div class="task-estimate" style="padding: 5px 10px; border: 1px solid #cccccc; background: #f2f2f4; color: #000; border-radius: 20px; font-size: 1.2rem; text-align: center; min-width: 50px;">
										${task.estimation_time || 0}h
									</div>
									
									<div class="task-due-date" style="padding: 5px 10px; border: 1px solid #cccccc; background: #f2f2f4; color: #000; border-radius: 20px; font-size: 1.2rem; text-align: center; min-width: 80px;" title="Due Date">
										${task.estimated_end_time ? new Date(task.estimated_end_time).toLocaleDateString('en-GB', {day: '2-digit', month: '2-digit'}) : 'No Due'}
									</div>
									<div class="user-avatar-container" style="width: 40px; flex-shrink: 0;">
										${task.assigned_to_photo ? 
											`<img src="${task.assigned_to_photo}" width="40" height="40" class="rounded" alt="${task.assigned_to_name}" title="${task.assigned_to_name}" />` :
											`<div class="user-avatar" style="background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #a0aec0; width: 40px; height: 40px; border-radius: 50%;"><i class="fas fa-user"></i></div>`
										}
									</div>
								</div>
							</div>
						`;
					}).join('');
					}

					// Show error function
					function showError(message) {
						document.getElementById('loadingState').innerHTML = `
							<div style="text-align: center; padding: 60px 20px; color: #dc3545;">
								<i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 16px;"></i>
								<h4>Error Loading Issues</h4>
								<p>${message}</p>
								<button onclick="location.reload()" class="btn btn-primary">Retry</button>
							</div>
						`;
					}
					
					// Search and filter functionality
					function initializeSearch() {
						const searchInput = document.getElementById('taskSearchInput');
						const clearIcon = document.getElementById('clearSearchIcon');
						const milestoneFilter = document.getElementById('milestoneFilter');
						
						searchInput.addEventListener('input', function() {
							const searchTerm = this.value.toLowerCase().trim();
							clearIcon.style.display = searchTerm ? 'inline' : 'none';
							filterTasks(searchTerm, milestoneFilter.value);
						});
						
						searchInput.addEventListener('keydown', function(e) {
							if (e.key === 'Escape') {
								clearSearch();
							}
						});
						
						// Milestone filter event listener (using jQuery for Select2)
						$('#milestoneFilter').on('change', function() {
							const searchTerm = searchInput.value.toLowerCase().trim();
							filterTasks(searchTerm, this.value);
						});
					}
					
					function filterTasks(searchTerm, milestoneFilter = 'all') {
						const accordionCards = document.querySelectorAll('.accordion-card');
						let totalVisible = 0;
						
						accordionCards.forEach(card => {
							const taskItems = card.querySelectorAll('.task-item');
							let visibleInCard = 0;
							
							taskItems.forEach(item => {
								const taskId = item.querySelector('.task-id')?.textContent.toLowerCase() || '';
								const taskTitle = item.querySelector('.task-title')?.textContent.toLowerCase() || '';
								const taskMilestone = item.querySelector('.task-milestone')?.textContent || '';
								
								// For description search, we'll need to check if any of the visible text matches
								const allText = item.textContent.toLowerCase();
								
								// Check search term match
								const searchMatches = !searchTerm || 
									taskId.includes(searchTerm) || 
									taskTitle.includes(searchTerm) || 
									allText.includes(searchTerm);
								
								// Check milestone filter match
								let milestoneMatches = true;
								if (milestoneFilter !== 'all') {
									// Get milestone ID from the task data attribute or extract from onclick
									const onclickAttr = item.getAttribute('onclick');
									const taskIdMatch = onclickAttr?.match(/openTaskModal\((\d+)\)/);
									
									// For now, we'll check if milestone text contains the selected milestone
									// This is a simplified approach - ideally we'd have milestone ID in data attribute
									if (taskMilestone === '' || taskMilestone === '-') {
										milestoneMatches = false;
									} else {
										// Check if the milestone text matches (this is a basic implementation)
										// You might want to store milestone ID as data attribute for better filtering
										milestoneMatches = item.dataset.milestoneId === milestoneFilter;
									}
								}
								
								if (searchMatches && milestoneMatches) {
									item.style.display = 'flex';
									visibleInCard++;
									totalVisible++;
								} else {
									item.style.display = 'none';
								}
							});
							
							// Update item count in accordion header
							const itemCount = card.querySelector('.item-count');
							if (itemCount) {
								const originalCount = taskItems.length;
								if (searchTerm || milestoneFilter !== 'all') {
									itemCount.textContent = `${visibleInCard}/${originalCount} items`;
								} else {
									itemCount.textContent = `${originalCount} items`;
								}
							}
							
							// Show/hide accordion card based on visible items
							if ((searchTerm || milestoneFilter !== 'all') && visibleInCard === 0) {
								card.style.display = 'none';
							} else {
								card.style.display = 'block';
								// Auto-expand accordion if search results found
								if ((searchTerm || milestoneFilter !== 'all') && visibleInCard > 0) {
									const header = card.querySelector('.accordion-header');
									const body = card.querySelector('.accordion-body');
									if (header && body && !header.classList.contains('active')) {
										header.classList.add('active');
										body.classList.add('active');
									}
								}
							}
						});
						
						// Show search results summary
						showSearchSummary(searchTerm, totalVisible, milestoneFilter);
					}
					
					function showSearchSummary(searchTerm, totalVisible, milestoneFilter = 'all') {
						let summaryDiv = document.getElementById('searchSummary');
						if (!summaryDiv) {
							summaryDiv = document.createElement('div');
							summaryDiv.id = 'searchSummary';
							summaryDiv.style.cssText = 'margin: 10px 0; padding: 8px 12px; background: #e3f2fd; border-radius: 4px; font-size: 14px; color: #1976d2;';
							document.querySelector('.tracker-header').appendChild(summaryDiv);
						}
						
						let summaryText = '';
						if (searchTerm && milestoneFilter !== 'all') {
							const milestoneText = document.querySelector(`#milestoneFilter option[value="${milestoneFilter}"]`)?.textContent || 'Selected Milestone';
							summaryText = `<i class="fas fa-search"></i> Found ${totalVisible} task${totalVisible !== 1 ? 's' : ''} matching "${searchTerm}" in ${milestoneText}`;
						} else if (searchTerm) {
							summaryText = `<i class="fas fa-search"></i> Found ${totalVisible} task${totalVisible !== 1 ? 's' : ''} matching "${searchTerm}"`;
						} else if (milestoneFilter !== 'all') {
							const milestoneText = document.querySelector(`#milestoneFilter option[value="${milestoneFilter}"]`)?.textContent || 'Selected Milestone';
							summaryText = `<i class="fas fa-flag-checkered"></i> Showing ${totalVisible} task${totalVisible !== 1 ? 's' : ''} in ${milestoneText}`;
						}
						
						if (summaryText) {
							summaryDiv.innerHTML = summaryText;
							summaryDiv.style.display = 'block';
						} else {
							summaryDiv.style.display = 'none';
						}
					}
					
					function clearSearch() {
						const searchInput = document.getElementById('taskSearchInput');
						const clearIcon = document.getElementById('clearSearchIcon');
						
						searchInput.value = '';
						clearIcon.style.display = 'none';
						filterTasks('', $('#milestoneFilter').val());
						searchInput.focus();
					}
					
					// Format date and time for display
					function formatDateTime(dateTimeString) {
						if (!dateTimeString) return 'N/A';
						const date = new Date(dateTimeString);
						return date.toLocaleDateString() + ' at ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
					}
			</script>

        </div>
    </div>
</div>
        </div>
    </div>
</div>

<style>
/* Hidden scrollbar styles */
::-webkit-scrollbar {
	width: 0px;
	height: 0px;
	background: transparent;
}
* {
	scrollbar-width: none;
	-ms-overflow-style: none;
}

/* Loading spinner animation */
@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}
</style>