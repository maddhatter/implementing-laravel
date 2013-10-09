<?php namespace Impl\Repo\Article;

use Impl\Repo\Tag\TagInterface;
use Illuminate\Database\Eloquent\Model;

class EloquentArticle implements ArticleInterface{

	protected $article;
	protected $tag;

	//Class dependency: Eloquent model and impementation of TagInterface
	public function __construct(Model $article, TagInterface $tag){
		$this->article = $article;
		$this->tag     = $tag;
	}

	public function byPage($page=1, $limit=10){
		$articles = $this->article->with('tags')
			->where('status_id', 1)
			->orderBy('created_at', 'desc')
			->skip($limit * ($page-1))
			->take($limit)
			->get();

		//create object to return data useful for pagination
		$data = new \StdClass();
		$data->items = $articles->all();
		$data->totalItems = $this->totalArticles();

		return $data;
	}

	public function bySlug($slug){
		return $this->article->with('tags')
			->where('status_id', 1)
			->where('slug', $slug)
			->first();
	}

	public function byTag($tag, $page=1, $limit=10){
		$foundTag = $this->tag->bySlug($tag);

		if(!$foundTag){
			$data = new \StdClass();
			$data->items = array();
			$data->totalItems = 0;

			return $data;
		}

		$articles = $this->tag->articles()
			->where('articles.status_id', 1)
			->orderBy('articles.created_at', 'desc')
			->skip($limit * ($page-1))
			->take($limit)
			->get();

		$data = new \StdClass();
		$data->items = $articles->all();
		$data->totalItems = $this->totalByTag();

		return $data;
	}

	protected totalArticles(){
		return $this->article->where('status_id', 1)->count();
	}

	protected totalByTag($tag){
		return $this->tag->bySlug($tag)
			->articles()
			->where('status_id', 1)
			->count();
	}
}