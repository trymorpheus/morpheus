<?php

namespace DynamicCRUD\Comments;

class CommentRenderer
{
    private CommentManager $manager;
    private bool $allowReplies;

    public function __construct(CommentManager $manager, bool $allowReplies = true)
    {
        $this->manager = $manager;
        $this->allowReplies = $allowReplies;
    }

    public function render(int $postId): string
    {
        $comments = $this->manager->getComments($postId);
        $count = $this->manager->getCount($postId);

        $html = $this->renderStyles();
        $html .= '<div class="comments-section">';
        $html .= sprintf('<h3>💬 %d Comment%s</h3>', $count, $count !== 1 ? 's' : '');
        
        if (empty($comments)) {
            $html .= '<p class="no-comments">No comments yet. Be the first to comment!</p>';
        } else {
            $html .= '<div class="comments-list">';
            foreach ($comments as $comment) {
                $html .= $this->renderComment($comment);
            }
            $html .= '</div>';
        }
        
        $html .= $this->renderForm($postId);
        $html .= '</div>';
        $html .= $this->renderScripts();

        return $html;
    }

    private function renderComment(array $comment, int $depth = 0): string
    {
        $marginLeft = $depth * 40;
        $avatar = $this->getGravatar($comment['author_email'], 48);
        $date = date('M j, Y', strtotime($comment['created_at']));
        
        $html = sprintf(
            '<div class="comment" style="margin-left: %dpx;">
                <img src="%s" class="comment-avatar" alt="%s">
                <div class="comment-content">
                    <div class="comment-header">
                        <strong>%s</strong>
                        <span class="comment-date">%s</span>
                    </div>
                    <div class="comment-text">%s</div>',
            $marginLeft,
            $avatar,
            htmlspecialchars($comment['author_name']),
            htmlspecialchars($comment['author_name']),
            $date,
            nl2br(htmlspecialchars($comment['content']))
        );

        if ($this->allowReplies && $depth < 3) {
            $html .= sprintf(
                '<button class="reply-btn" onclick="showReplyForm(%d)">Reply</button>
                <div id="reply-form-%d" class="reply-form" style="display: none;">
                    %s
                </div>',
                $comment['id'],
                $comment['id'],
                $this->renderForm($comment['post_id'], $comment['id'])
            );
        }

        $html .= '</div></div>';

        // Render replies
        if (!empty($comment['replies'])) {
            foreach ($comment['replies'] as $reply) {
                $html .= $this->renderComment($reply, $depth + 1);
            }
        }

        return $html;
    }

    private function renderForm(int $postId, ?int $parentId = null): string
    {
        $formId = $parentId ? "reply-form-$parentId" : 'comment-form';
        $title = $parentId ? 'Reply' : 'Leave a Comment';

        return sprintf(
            '<form id="%s" class="comment-form" method="POST" action="?action=add_comment">
                <input type="hidden" name="post_id" value="%d">
                <input type="hidden" name="parent_id" value="%s">
                <h4>%s</h4>
                <div class="form-group">
                    <input type="text" name="author_name" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="author_email" placeholder="Your Email" required>
                </div>
                <div class="form-group">
                    <textarea name="content" rows="4" placeholder="Your Comment" required></textarea>
                </div>
                <button type="submit" class="submit-btn">Post Comment</button>
                %s
            </form>',
            $formId,
            $postId,
            $parentId ?? '',
            $title,
            $parentId ? '<button type="button" class="cancel-btn" onclick="hideReplyForm(' . $parentId . ')">Cancel</button>' : ''
        );
    }

    private function renderStyles(): string
    {
        return <<<'CSS'
<style>
.comments-section { max-width: 800px; margin: 40px auto; padding: 20px; }
.comments-section h3 { font-size: 24px; margin-bottom: 20px; }
.no-comments { color: #999; font-style: italic; }
.comments-list { margin-bottom: 40px; }
.comment { display: flex; gap: 15px; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; }
.comment-avatar { width: 48px; height: 48px; border-radius: 50%; }
.comment-content { flex: 1; }
.comment-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
.comment-date { color: #999; font-size: 14px; }
.comment-text { line-height: 1.6; margin-bottom: 10px; }
.reply-btn { background: none; border: none; color: #667eea; cursor: pointer; font-size: 14px; padding: 0; }
.reply-btn:hover { text-decoration: underline; }
.reply-form { margin-top: 15px; }
.comment-form { background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; }
.comment-form h4 { margin-bottom: 15px; }
.form-group { margin-bottom: 15px; }
.form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; }
.form-group textarea { resize: vertical; }
.submit-btn { background: #667eea; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; }
.submit-btn:hover { background: #5568d3; }
.cancel-btn { background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-left: 10px; }
.cancel-btn:hover { background: #5a6268; }
</style>
CSS;
    }

    private function renderScripts(): string
    {
        return <<<'JS'
<script>
function showReplyForm(commentId) {
    document.getElementById('reply-form-' + commentId).style.display = 'block';
}
function hideReplyForm(commentId) {
    document.getElementById('reply-form-' + commentId).style.display = 'none';
}
</script>
JS;
    }

    private function getGravatar(string $email, int $size = 48): string
    {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/$hash?s=$size&d=mp";
    }
}
