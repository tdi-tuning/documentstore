<?php namespace DocumentStore;

use DocumentStore\Models\File;
use DocumentStore\Models\Revision;


class DBStorageManager
{
    /**
     * Create record of new file
     *
     * @param  object $result dropbox response
     * @return bool
     */
    public function create($result, $meta=null)
    {

        \DB::beginTransaction();

        try {
            $file = new File;
            $file->path = $result->path_lower;
            $file->dp_id = $result->id;
            $file->save();

            $rev = new Revision;
            $rev->rev = $result->rev;
            $rev->type = 'C';
            $rev->file_id = $file->id;
            if ($meta) $rev->meta_id = $meta->id;
            $rev->save();
            
            $file->revision_id = $rev->id;
            $file->save();
        } catch (\Exception $e) {
           \DB::rollback();
           throw $e;
        }

        \DB::commit();

        return true;
    }

    /**
     * Add an update revision
     *
     * @param  object $result dropbox response
     * @return bool
     */
    public function update($result, $meta=null)
    {
        return $this->newRevision($result, 'U', $meta);
    }

    /**
     * Add a delete revision
     *
     * @param  object $result dropbox response
     * @return bool
     */
    public function delete($result, $meta=null)
    {
        return $this->newRevision($result, 'D', $meta);
    }

    /**
     * Restore revision
     *
     * @param  object $result dropbox response
     * @return bool
     */
    public function restore($result, $rev)
    {
        $file = File::where('dp_id', $result->id)->first();
        $rev  = $file->revisions()->where('rev', $rev)->first();
        $file->revision_id = $rev->id;
        $file->save();

        return true;
    }

    /**
     * Check if file exists
     *
     * @param  string $path dropbox file
     * @return bool
     */
    public function exists($path)
    {
        return File::where('path', $path)->first() !== null;
    }

    /**
     * Get current revision of file
     *
     * @param  string $path dropbox file
     * @return array
     */
    public function currentRevision($path)
    {
        return File::where('path', $path)->first()->revision->rev;
    }

    /**
     * Get revisions of file
     *
     * @param  string $path dropbox file
     * @return array
     */
    public function revisions($path)
    {
        $file = File::where('path', $path)->first();
        $current = $file->revision_id;
        $revisions = $file->revisions->toArray();
        return array_map(function ($el) use($current) {
            $el['current'] = $el['id'] === $current;
            return $el;
        }, $revisions);
    }

    /**
     * Add new revision
     *
     * @param  object $result dropbox response
     * @param  string $type revision type
     * @return bool
     */
    private function newRevision($result, $type, $meta=null)
    {
        $file = File::where('dp_id', $result->id)->first();

        \DB::beginTransaction();

        try {
            $rev = new Revision;
            $rev->rev = $result->rev;
            $rev->type = $type;
            $rev->file_id = $file->id;
            if ($meta) $rev->meta_id = $meta->id;
            $rev->save();
            
            $file->revision_id = $rev->id;
            $file->save();

        } catch (\Illuminate\Database\QueryException $e) {
           \DB::rollback();
        } catch (\Exception $e) {
           \DB::rollback();
           throw $e;
        }

        \DB::commit();

        return true;
    }
}
