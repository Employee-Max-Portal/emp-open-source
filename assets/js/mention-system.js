/**
 * Mention System for Comments
 * Allows users to mention other users in comments using @username syntax
 */

class MentionSystem {
    constructor(textareaId, dropdownId, baseUrl) {
        this.textarea = document.getElementById(textareaId);
        this.dropdown = document.getElementById(dropdownId);
        this.baseUrl = baseUrl;
        this.mentionUsers = [];
        this.selectedMentionIndex = -1;
        this.mentionStartPos = -1;
        this.mentionQuery = '';
        
        this.init();
    }
    
    init() {
        if (!this.textarea || !this.dropdown) return;
        
        // Remove existing listeners to prevent duplicates
        this.textarea.removeEventListener('input', this.handleMentionInput.bind(this));
        this.textarea.removeEventListener('keydown', this.handleMentionKeydown.bind(this));
        document.removeEventListener('click', this.closeMentionDropdown.bind(this));
        
        // Add fresh listeners
        this.textarea.addEventListener('input', this.handleMentionInput.bind(this));
        this.textarea.addEventListener('keydown', this.handleMentionKeydown.bind(this));
        document.addEventListener('click', this.closeMentionDropdown.bind(this));
        
        // Reset mention state
        this.mentionUsers = [];
        this.selectedMentionIndex = -1;
        this.mentionStartPos = -1;
        this.mentionQuery = '';
    }
    
    handleMentionInput(e) {
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
            this.mentionStartPos = atPos;
            this.mentionQuery = text.substring(atPos + 1, cursorPos);
            this.showMentionDropdown(this.mentionQuery);
        } else {
            this.hideMentionDropdown();
            clearTimeout(this.mentionTimeout);
        }
    }
    
    handleMentionKeydown(e) {
        if (this.dropdown.style.display === 'none') return;

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedMentionIndex = Math.min(this.selectedMentionIndex + 1, this.mentionUsers.length - 1);
                this.updateMentionSelection();
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.selectedMentionIndex = Math.max(this.selectedMentionIndex - 1, 0);
                this.updateMentionSelection();
                break;
            case 'Enter':
                e.preventDefault();
                if (this.selectedMentionIndex >= 0) {
                    this.selectMention(this.mentionUsers[this.selectedMentionIndex]);
                }
                break;
            case 'Escape':
                this.hideMentionDropdown();
                break;
        }
    }
    
    showMentionDropdown(query) {
        // Try planner endpoint first, fallback to tracker
        const endpoint = window.location.pathname.includes('planner') ? 'planner' : 'tracker';
        
        // Use jQuery if available (for planner) as it has CSRF setup
        if (typeof $ !== 'undefined') {
            // Add a small delay to prevent rapid requests
            clearTimeout(this.mentionTimeout);
            this.mentionTimeout = setTimeout(() => {
                $.ajax({
                    url: `${this.baseUrl}/${endpoint}/get_mention_users`,
                    method: 'POST',
                    data: { search: query },
                    dataType: 'json',
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                })
                .done((users) => {
                    this.mentionUsers = users || [];
                    this.selectedMentionIndex = users && users.length > 0 ? 0 : -1;
                    this.renderMentionDropdown(users || []);
                })
                .fail((xhr, status, error) => {
                    console.error('Error fetching users:', error);
                    this.hideMentionDropdown();
                });
            }, 300);
        } else {
            // Fallback to fetch with manual CSRF handling
            const getCsrfToken = () => {
                const cookies = document.cookie.split(';');
                for (let cookie of cookies) {
                    const [name, value] = cookie.trim().split('=');
                    if (name.includes('csrf')) {
                        return decodeURIComponent(value);
                    }
                }
                return '';
            };
            
            const formData = new FormData();
            formData.append('search', query);
            const csrfToken = getCsrfToken();
            if (csrfToken) {
                formData.append('csrf_test_name', csrfToken);
            }
            
            clearTimeout(this.mentionTimeout);
            this.mentionTimeout = setTimeout(() => {
                fetch(`${this.baseUrl}/${endpoint}/get_mention_users`, {
                    method: 'POST',
                    body: formData,
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(users => {
                    this.mentionUsers = users || [];
                    this.selectedMentionIndex = users && users.length > 0 ? 0 : -1;
                    this.renderMentionDropdown(users || []);
                })
                .catch(error => {
                    console.error('Error fetching users:', error);
                    this.hideMentionDropdown();
                });
            }, 300);
        }
    }
    
    renderMentionDropdown(users) {
        if (users.length === 0) {
            this.dropdown.style.display = 'none';
            return;
        }

        this.dropdown.innerHTML = users.map((user, index) => `
            <div class="mention-item ${index === this.selectedMentionIndex ? 'selected' : ''}" 
                 data-user-id="${user.id}" data-user-name="${user.name}">
                ${user.photo ? `<img src="${user.photo}" class="mention-avatar" alt="${user.name}">` : '<div class="mention-avatar" style="background: #ddd;"></div>'}
                <span class="mention-name">${user.name}</span>
            </div>
        `).join('');
        
        // Add click listeners
        this.dropdown.querySelectorAll('.mention-item').forEach((item, index) => {
            item.addEventListener('click', () => {
                const user = {
                    id: item.dataset.userId,
                    name: item.dataset.userName
                };
                this.selectMention(user);
            });
        });
        
        this.dropdown.style.display = 'block';
    }
    
    updateMentionSelection() {
        const items = this.dropdown.querySelectorAll('.mention-item');
        items.forEach((item, index) => {
            item.classList.toggle('selected', index === this.selectedMentionIndex);
        });
    }
    
    selectMention(user) {
        const text = this.textarea.value;
        const beforeMention = text.substring(0, this.mentionStartPos);
        const afterMention = text.substring(this.textarea.selectionStart);
        const mentionText = `@[${user.id}]${user.name}`;

        this.textarea.value = beforeMention + mentionText + afterMention;
        const newCursorPos = beforeMention.length + mentionText.length;
        this.textarea.setSelectionRange(newCursorPos, newCursorPos);
        this.textarea.focus();

        this.hideMentionDropdown();
    }
    
    hideMentionDropdown() {
        if (this.dropdown) {
            this.dropdown.style.display = 'none';
            this.dropdown.innerHTML = '';
        }
        this.selectedMentionIndex = -1;
        this.mentionStartPos = -1;
        this.mentionQuery = '';
        this.mentionUsers = [];
        clearTimeout(this.mentionTimeout);
    }
    
    closeMentionDropdown(e) {
        if (!e.target.closest('.mention-container')) {
            this.hideMentionDropdown();
        }
    }
    
    // Static method to process mentions in text for display
    static processMentions(text) {
        return text.replace(/@\[(\d+)\]([^@\s]+)/g, '<span class="mentioned-user">@$2</span>');
    }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MentionSystem;
}