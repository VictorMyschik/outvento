# Album Media Comments API

This module adds scalable comments for `album_media` with lazy-loaded replies and soft deletes.

## Endpoints

- `GET /api/v1/album/media/{mediaId}/comments?per_page=20&page=1`
- `GET /api/v1/album/media/{mediaId}/comments/{commentId}/replies?per_page=20&page=1`
- `POST /api/v1/album/media/{mediaId}/comments`
- `POST /api/v1/album/media/{mediaId}/comments/{commentId}/replies`
- `DELETE /api/v1/album/media/{mediaId}/comments/{commentId}`

Write endpoints require `auth:sanctum` and `throttle:album-comments-write`.

## Data Model

- Table: `album_media_comments`
- Pattern: adjacency list (`parent_id`) with max depth 2
- Soft delete: enabled (`deleted_at`)
- Denormalization:
  - `album_media.comments_count`
  - `album_media_comments.replies_count`

## Performance Notes

- Root and reply lists are paginated independently.
- Author data is eager-loaded (`with user`) to avoid N+1.
- Read cache uses short TTL and versioned keys for fast invalidation after writes.

## Integration with AlbumService

`AlbumCommentService` delegates access checks to `AlbumService`:

- `AlbumService::canViewAlbumMedia()`
- `AlbumService::canCommentOnAlbumMedia()`

This keeps album visibility and ownership rules centralized.

