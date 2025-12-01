/**
 * THODZ - Modern Social Media Application
 * Main JavaScript File
 */

const App = {
    baseUrl: '/',
    currentUser: null,
    
    init() {
        this.bindEvents();
        this.initSearch();
        this.initDropdowns();
        this.initModals();
        this.initPosts();
        this.initImagePreview();
    },

    // ============================================
    // EVENT BINDINGS
    // ============================================
    bindEvents() {
        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown-trigger') && !e.target.closest('.dropdown-menu')) {
                document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
            if (!e.target.closest('.post-menu-btn') && !e.target.closest('.post-menu')) {
                document.querySelectorAll('.post-menu.active').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
            if (!e.target.closest('.search-container')) {
                document.querySelectorAll('.search-results.active').forEach(results => {
                    results.classList.remove('active');
                });
            }
        });

        // Auto-resize textareas
        document.querySelectorAll('textarea[data-autoresize]').forEach(textarea => {
            textarea.addEventListener('input', () => {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            });
        });
    },

    // ============================================
    // SEARCH FUNCTIONALITY
    // ============================================
    initSearch() {
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        
        if (!searchInput || !searchResults) return;

        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                searchResults.classList.remove('active');
                return;
            }

            searchTimeout = setTimeout(() => {
                this.performSearch(query);
            }, 300);
        });

        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length >= 2) {
                searchResults.classList.add('active');
            }
        });
    },

    async performSearch(query) {
        const searchResults = document.getElementById('searchResults');
        
        try {
            const response = await fetch(`${this.baseUrl}api/process.php?action=thodzsearch`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `searchfor=${encodeURIComponent(query)}`
            });
            
            const html = await response.text();
            
            if (html.trim()) {
                searchResults.innerHTML = html;
            } else {
                searchResults.innerHTML = '<div class="search-no-results">No users found</div>';
            }
            searchResults.classList.add('active');
        } catch (error) {
            console.error('Search error:', error);
        }
    },

    // ============================================
    // DROPDOWN MENUS
    // ============================================
    initDropdowns() {
        document.querySelectorAll('.dropdown-trigger').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const menu = trigger.nextElementSibling;
                if (menu && menu.classList.contains('dropdown-menu')) {
                    menu.classList.toggle('active');
                }
            });
        });
    },

    // ============================================
    // MODALS
    // ============================================
    initModals() {
        // Open create post modal
        document.querySelectorAll('[data-modal="createPost"]').forEach(trigger => {
            trigger.addEventListener('click', () => this.openModal('createPostModal'));
        });

        // Close modal buttons
        document.querySelectorAll('.modal-close, .modal-cancel').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.modal-overlay');
                if (modal) this.closeModal(modal.id);
            });
        });

        // Close modal on overlay click
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) this.closeModal(overlay.id);
            });
        });

        // Create post form submission
        const createPostForm = document.getElementById('createPostForm');
        if (createPostForm) {
            createPostForm.addEventListener('submit', (e) => this.handleCreatePost(e));
        }

        // Edit post form submission
        const editPostForm = document.getElementById('editPostForm');
        if (editPostForm) {
            editPostForm.addEventListener('submit', (e) => this.handleEditPost(e));
        }
    },

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            
            // Reset form if exists
            const form = modal.querySelector('form');
            if (form) form.reset();
            
            // Clear image preview
            const preview = modal.querySelector('.image-preview-container');
            if (preview) {
                preview.classList.remove('active');
                const img = preview.querySelector('.image-preview');
                if (img) img.src = '';
            }
        }
    },

    // ============================================
    // IMAGE PREVIEW
    // ============================================
    initImagePreview() {
        document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
            input.addEventListener('change', (e) => {
                const previewId = input.dataset.preview;
                const preview = document.getElementById(previewId);
                const container = preview?.closest('.image-preview-container');
                
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        if (preview) {
                            preview.src = event.target.result;
                            container?.classList.add('active');
                        }
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        });

        // Edit post image input
        const editPostImageInput = document.getElementById('editPostImageInput');
        if (editPostImageInput) {
            editPostImageInput.addEventListener('change', (e) => {
                const preview = document.getElementById('editPostImagePreview');
                const container = document.getElementById('editImagePreviewContainer');
                
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        if (preview) preview.src = event.target.result;
                        if (container) container.style.display = 'block';
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }

        // Remove image preview
        document.querySelectorAll('.image-preview-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                const container = btn.closest('.image-preview-container');
                const form = container?.closest('form');
                const input = form?.querySelector('input[type="file"]');
                
                if (container) {
                    container.classList.remove('active');
                    container.style.display = 'none';
                }
                if (input) input.value = '';
                
                // Clear the preview image
                const preview = container?.querySelector('.image-preview');
                if (preview) preview.src = '';
                
                // Set remove_image flag for edit form
                const removeImageInput = form?.querySelector('#editRemoveImage');
                if (removeImageInput) {
                    removeImageInput.value = '1';
                }
            });
        });
    },

    // ============================================
    // POST FUNCTIONALITY
    // ============================================
    initPosts() {
        // Post menu buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.post-menu-btn')) {
                e.stopPropagation();
                const btn = e.target.closest('.post-menu-btn');
                const menu = btn.nextElementSibling;
                
                // Close other menus
                document.querySelectorAll('.post-menu.active').forEach(m => {
                    if (m !== menu) m.classList.remove('active');
                });
                
                menu?.classList.toggle('active');
            }
        });

        // Edit post buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.edit-post-btn')) {
                const btn = e.target.closest('.edit-post-btn');
                const postId = btn.dataset.postId;
                const postText = btn.dataset.postText || '';
                this.openEditModal(postId, postText);
            }
        });

        // Delete post buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.delete-post-btn')) {
                const btn = e.target.closest('.delete-post-btn');
                const postId = btn.dataset.postId;
                this.confirmDeletePost(postId);
            }
        });

        // Toggle comments section
        document.addEventListener('click', (e) => {
            // Click on comment button or view comments link
            if (e.target.closest('.comment-toggle-btn') || e.target.closest('.view-comments-btn') || e.target.closest('.post-comments-count')) {
                const postCard = e.target.closest('.post');
                if (postCard) {
                    const postId = postCard.dataset.postId;
                    const commentsSection = postCard.querySelector('.post-comments');
                    const commentInput = postCard.querySelector('.comment-input-wrapper');
                    
                    if (commentsSection) {
                        commentsSection.classList.toggle('active');
                    }
                    if (commentInput) {
                        commentInput.style.display = commentsSection?.classList.contains('active') ? 'flex' : 'none';
                        // Focus on input when opening
                        if (commentsSection?.classList.contains('active')) {
                            const input = commentInput.querySelector('.comment-input');
                            if (input) input.focus();
                        }
                    }
                }
            }
        });

        // Like buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.like-btn')) {
                const btn = e.target.closest('.like-btn');
                const type = btn.dataset.type || 'post';
                const id = btn.dataset.id;
                this.toggleLike(type, id, btn);
            }
        });

        // Show likes list
        document.addEventListener('click', (e) => {
            if (e.target.closest('.show-likes-btn')) {
                const btn = e.target.closest('.show-likes-btn');
                const postId = btn.dataset.postId;
                if (postId) {
                    this.showLikesList(postId);
                }
            }
        });

        // Comment submission
        document.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey && e.target.classList.contains('comment-input')) {
                e.preventDefault();
                const form = e.target.closest('form');
                if (form) this.handleAddComment(form);
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target.closest('.comment-submit.active')) {
                const btn = e.target.closest('.comment-submit');
                const form = btn.closest('form');
                if (form) this.handleAddComment(form);
            }
        });

        // Comment input change - enable/disable submit
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('comment-input')) {
                const form = e.target.closest('form');
                const submitBtn = form?.querySelector('.comment-submit');
                if (submitBtn) {
                    if (e.target.value.trim()) {
                        submitBtn.classList.add('active');
                    } else {
                        submitBtn.classList.remove('active');
                    }
                }
            }
        });
    },

    async handleCreatePost(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Posting...';

        try {
            const response = await fetch(`${this.baseUrl}api/ajax.php?action=post`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.closeModal('createPostModal');
                await this.loadNewPost(data.pid);
                form.reset();
            } else {
                alert(data.message || 'Failed to create post');
            }
        } catch (error) {
            console.error('Create post error:', error);
            alert('Failed to create post');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Post';
        }
    },

    async loadNewPost(postId) {
        try {
            const response = await fetch(`${this.baseUrl}api/process.php?action=addpost`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `pid=${postId}`
            });
            
            const html = await response.text();
            
            if (html.trim()) {
                const postSection = document.getElementById('postSection');
                if (postSection) {
                    postSection.insertAdjacentHTML('afterbegin', html);
                }
            }
        } catch (error) {
            console.error('Load post error:', error);
        }
    },

    openEditModal(postId, postText) {
        const modal = document.getElementById('editPostModal');
        if (!modal) return;

        const textarea = modal.querySelector('#editPostText');
        const postIdInput = modal.querySelector('#editPostId');
        const removeImageInput = modal.querySelector('#editRemoveImage');
        const imagePreviewContainer = modal.querySelector('#editImagePreviewContainer');
        const imagePreview = modal.querySelector('#editPostImagePreview');
        const imageInput = modal.querySelector('#editPostImageInput');
        
        if (textarea) textarea.value = this.decodeHtml(postText);
        if (postIdInput) postIdInput.value = postId;
        if (removeImageInput) removeImageInput.value = '0';
        
        // Reset image preview
        if (imagePreviewContainer) imagePreviewContainer.style.display = 'none';
        if (imagePreview) imagePreview.src = '';
        if (imageInput) imageInput.value = '';
        
        // Show existing image if post has one
        const postElement = document.querySelector(`[data-post-id="${postId}"]`);
        if (postElement) {
            const existingImage = postElement.querySelector('.post-image');
            if (existingImage && existingImage.src) {
                if (imagePreview) imagePreview.src = existingImage.src;
                if (imagePreviewContainer) imagePreviewContainer.style.display = 'block';
            }
        }
        
        this.openModal('editPostModal');
    },

    async handleEditPost(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const postId = form.querySelector('#editPostId')?.value;
        const removeImage = form.querySelector('#editRemoveImage')?.value === '1';
        const newImageInput = form.querySelector('#editPostImageInput');
        const hasNewImage = newImageInput && newImageInput.files && newImageInput.files[0];
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        try {
            const response = await fetch(`${this.baseUrl}api/ajax.php?action=editpost`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                const newText = form.querySelector('#editPostText').value;
                
                // Update post content in DOM immediately
                const postElement = document.querySelector(`[data-post-id="${postId}"]`);
                if (postElement) {
                    // Update the visible text
                    let textElement = postElement.querySelector('.post-text');
                    let postContent = postElement.querySelector('.post-content');
                    
                    if (newText) {
                        if (textElement) {
                            textElement.innerHTML = this.escapeHtml(newText).replace(/\n/g, '<br>');
                        } else if (postContent) {
                            postContent.innerHTML = `<p class="post-text">${this.escapeHtml(newText).replace(/\n/g, '<br>')}</p>`;
                        } else {
                            // Create post content section
                            postContent = document.createElement('div');
                            postContent.className = 'post-content';
                            postContent.innerHTML = `<p class="post-text">${this.escapeHtml(newText).replace(/\n/g, '<br>')}</p>`;
                            const postHeader = postElement.querySelector('.post-header');
                            if (postHeader) {
                                postHeader.insertAdjacentElement('afterend', postContent);
                            }
                        }
                    } else if (postContent) {
                        // Remove text if empty
                        const pElement = postContent.querySelector('.post-text');
                        if (pElement) pElement.remove();
                    }
                    
                    // Update the edit button's data attribute for future edits
                    const editBtn = postElement.querySelector('.edit-post-btn');
                    if (editBtn) {
                        editBtn.dataset.postText = newText;
                    }
                    
                    // Handle image updates
                    const existingImage = postElement.querySelector('.post-image');
                    
                    if (removeImage && !hasNewImage) {
                        // Remove image from DOM
                        if (existingImage) {
                            existingImage.remove();
                        }
                    } else if (hasNewImage) {
                        // Update/add image
                        const reader = new FileReader();
                        reader.onload = function(evt) {
                            if (existingImage) {
                                existingImage.src = evt.target.result;
                            } else {
                                const img = document.createElement('img');
                                img.src = evt.target.result;
                                img.alt = 'Post image';
                                img.className = 'post-image';
                                const postContentEl = postElement.querySelector('.post-content');
                                if (postContentEl) {
                                    postContentEl.insertAdjacentElement('afterend', img);
                                } else {
                                    const postHeader = postElement.querySelector('.post-header');
                                    if (postHeader) {
                                        postHeader.insertAdjacentElement('afterend', img);
                                    }
                                }
                            }
                        };
                        reader.readAsDataURL(newImageInput.files[0]);
                    }
                }
                
                // Close modal and reset form
                this.closeModal('editPostModal');
                form.reset();
            } else {
                alert(data.message || 'Failed to update post');
            }
        } catch (error) {
            console.error('Edit post error:', error);
            alert('Failed to update post');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save';
        }
    },

    confirmDeletePost(postId) {
        const modal = document.getElementById('confirmModal');
        if (!modal) {
            if (confirm('Are you sure you want to delete this post?')) {
                this.deletePost(postId);
            }
            return;
        }

        const confirmBtn = modal.querySelector('.confirm-delete-btn');
        if (confirmBtn) {
            confirmBtn.onclick = () => {
                this.deletePost(postId);
                this.closeModal('confirmModal');
            };
        }
        
        this.openModal('confirmModal');
    },

    async deletePost(postId) {
        try {
            const response = await fetch(`${this.baseUrl}api/ajax.php?action=deletepost`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `postid=${postId}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                const postElement = document.querySelector(`[data-post-id="${postId}"]`);
                if (postElement) {
                    postElement.remove();
                }
            } else {
                alert('Failed to delete post');
            }
        } catch (error) {
            console.error('Delete post error:', error);
            alert('Failed to delete post');
        }
    },

    async toggleLike(type, id, btn) {
        try {
            const response = await fetch(`${this.baseUrl}api/ajax.php?action=like`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `type=${type}&pid=${id}`
            });
            
            const data = await response.json();
            
            if (type === 'user') {
                // Follow/Unfollow
                const isFollowing = btn.classList.contains('following');
                btn.classList.toggle('following');
                btn.classList.toggle('btn-primary', isFollowing);
                btn.classList.toggle('btn-success', !isFollowing);
                
                const icon = btn.querySelector('i');
                const text = btn.querySelector('span') || btn;
                
                if (isFollowing) {
                    if (icon) icon.className = 'fas fa-user-plus';
                    if (text.tagName === 'SPAN') text.textContent = 'Follow';
                    else btn.innerHTML = '<i class="fas fa-user-plus"></i> Follow';
                } else {
                    if (icon) icon.className = 'fas fa-user-check';
                    if (text.tagName === 'SPAN') text.textContent = 'Following';
                    else btn.innerHTML = '<i class="fas fa-user-check"></i> Following';
                }

                // Update follower count
                if (data.follower_count !== undefined) {
                    const countEl = document.getElementById('followerCount');
                    if (countEl) countEl.textContent = data.follower_count;
                }
            } else {
                // Like post/comment
                const isLiked = btn.classList.toggle('liked');
                const icon = btn.querySelector('i');
                
                // Toggle icon style
                if (icon) {
                    icon.className = isLiked ? 'fas fa-thumbs-up' : 'far fa-thumbs-up';
                }
                
                // Update like count
                if (data.counter !== undefined) {
                    const count = parseInt(data.counter);
                    
                    // Update the small count next to icon
                    const countEl = document.getElementById(`likesCount_${id}`);
                    if (countEl) countEl.textContent = count;
                    
                    // Update the likes text (e.g., "5 likes")
                    const likesTextEl = document.getElementById(`likesText_${id}`);
                    if (likesTextEl) likesTextEl.textContent = `${count} likes`;
                    
                    // Show/hide the likes wrapper based on count
                    const likesWrapper = document.getElementById(`likesWrapper_${id}`);
                    if (likesWrapper) {
                        likesWrapper.style.display = count > 0 ? '' : 'none';
                    }
                }
            }
        } catch (error) {
            console.error('Like error:', error);
        }
    },

    async handleAddComment(form) {
        const postId = form.dataset.postId;
        const textarea = form.querySelector('.comment-input');
        const text = textarea?.value.trim();
        
        if (!text || !postId) return;

        const formData = new FormData(form);
        
        try {
            const response = await fetch(`${this.baseUrl}api/ajax.php?action=addcomment`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                textarea.value = '';
                form.querySelector('.comment-submit')?.classList.remove('active');
                
                // Load new comment
                await this.loadNewComment(postId, data.commentid);
                
                // Update comment count
                const countEl = document.getElementById(`commentCount_${postId}`);
                if (countEl && data.comment_counter !== undefined) {
                    countEl.textContent = `${data.comment_counter} comments`;
                }
            }
        } catch (error) {
            console.error('Add comment error:', error);
        }
    },

    async loadNewComment(postId, commentId) {
        try {
            const response = await fetch(`${this.baseUrl}api/process.php?action=addcomment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `pid=${postId}&cid=${commentId}`
            });
            
            const html = await response.text();
            
            if (html.trim()) {
                const commentsSection = document.getElementById(`comments_${postId}`);
                if (commentsSection) {
                    commentsSection.insertAdjacentHTML('beforeend', html);
                }
            }
        } catch (error) {
            console.error('Load comment error:', error);
        }
    },

    // ============================================
    // FOLLOW FUNCTIONALITY
    // ============================================
    async followUser(targetUserId, btn) {
        await this.toggleLike('user', targetUserId, btn);
    },

    // ============================================
    // LIKES LIST
    // ============================================
    async showLikesList(postId) {
        const container = document.getElementById('likesListContainer');
        if (!container) return;
        
        // Show loading
        container.innerHTML = '<div class="likes-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        this.openModal('likesModal');
        
        try {
            const response = await fetch(`${this.baseUrl}api/process.php?action=getlikes`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `pid=${postId}`
            });
            
            const html = await response.text();
            
            if (html.trim()) {
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="likes-empty">No likes yet</div>';
            }
        } catch (error) {
            console.error('Load likes error:', error);
            container.innerHTML = '<div class="likes-error">Failed to load likes</div>';
        }
    },

    // ============================================
    // UTILITIES
    // ============================================
    decodeHtml(html) {
        const txt = document.createElement('textarea');
        txt.innerHTML = html;
        return txt.value;
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes}m`;
        if (hours < 24) return `${hours}h`;
        if (days < 7) return `${days}d`;
        
        return date.toLocaleDateString();
    }
};

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => App.init());

// Global functions for inline event handlers (backwards compatibility)
function doLike(type, id) {
    const btn = event?.target?.closest('.like-btn, .btn');
    App.toggleLike(type, id, btn);
}

function deleteComment(commentId) {
    if (confirm('Delete this comment?')) {
        App.deletePost(commentId);
    }
}
