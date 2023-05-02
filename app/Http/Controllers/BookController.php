<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'title' => ['sometimes', 'string'],
            'index_title' => ['sometimes', 'string'],
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        $return = Book::with([
            'userPublisher',
            'bookIndexes', 
        ]);

        if (isset($data['title']) && !empty($data['title'])) {
            $return->where('title', $data['title']);
        }

        if (isset($data['index_title']) && !empty($data['index_title'])) {
            $return->whereHas('bookIndexes', function ($q) use ($data) {
                $q->where('title', $data['index_title']);
            });
        }

        $return = collect($return->get());
        $return->map(function($book) {
            if ($book->bookIndexes()->exists()) {

                collect($book->bookIndexes)->map(function($bookIndex) {
                    return $this->lazyLoadInnerIndex($bookIndex);
                });

                // $book->bookIndexes->load('innerIndexes');
            }

            return $book;
        });


        // if ()

        return response([
            'data' => $return,
            'message' => 'Livros retornados com sucesso!',
        ]);
    }

    public function lazyLoadInnerIndex($bookIndex)
    {
        if ($bookIndex->innerIndexes()->exists()) {
            $bookIndex->innerIndexes->load('innerIndexes');

            collect($bookIndex->innerIndexes)->map(function($bookIndex) {
                $this->lazyLoadInnerIndex($bookIndex);
            });
        }

        return $bookIndex;
    }

    public function store(Request $request) 
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'title' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
    
            $book = new Book();
            $book->user_publisher_id = Auth::user()->id;
            $book->title = $data['title'];
            $book->save();
    
            collect($data['index'])->each(function ($index) use ($book) {
                $bookIndex = new BookIndex();
                $bookIndex->book_id = $book->id;
                $bookIndex->title = $index['title'];
                $bookIndex->page = $index['page'];
                $bookIndex->save();
    
                if (isset($index['sub_indexes']) && count($index['sub_indexes'])) {
                    $this->runSubIndexes(
                        $index['sub_indexes'],
                        $book->id,
                        $bookIndex->id
                    );
                }
            });
    
            DB::commit();
        } catch (ValidationException $e) {
            DB::rollback();
            return response([
                'message' => 'Payload com erro',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response('Erro na requisição', 400);
        }
    }

    private function runSubIndexes($subIndex, $bookId, $previousIndexId) 
    {
        collect($subIndex)->each(function($index) use ($bookId, $previousIndexId) {
            
            $validator = Validator::make($index, [
                'title' => ['required', 'string'],
                'page' => ['required', 'integer'],
            ]);
    
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            
            $bookIndex = new BookIndex();
            $bookIndex->book_id = $bookId;
            $bookIndex->index_id = $previousIndexId;
            $bookIndex->title = $index['title'];
            $bookIndex->page = $index['page'];
            $bookIndex->save();

            if (isset($index['sub_indexes']) && count($index['sub_indexes'])) {
                $this->runSubIndexes($index['sub_indexes'], $bookId, $bookIndex->id);
            }
        });

    }

    public function importIndexXml(Request $request, int $bookId) 
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'xml_file' => ['required', 'file'],
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $xmlName = md5(date('dmYHis')) . '.xml';
            $request->file('xml_file')->storeAs('/xml/imports/', $xmlName);
            $xml = simplexml_load_string(Storage::disk('local')->get('/xml/imports/' . $xmlName));
            
            Storage::disk('local')->delete('/xml/imports/' . $xmlName);
            $xmlCollection = collect(json_decode(json_encode($xml),true)['item']);
    
            $xmlCollection->each(function($item) use ($bookId) {
                $this->importXmlData($item, $bookId);
            });
    
            DB::commit();
            return response([
                "message" => "Xml de índices importado com sucesso!"
            ]);
        } catch (ValidationException $e) {
            DB::rollback();
            return response([
                'message' => 'Xml com erro',
                'error' => $e->getMessage()
            ], 422);
        }  catch (\Exception $e) {
            DB::rollBack();
            return response('Ocorreu um erro ao importar o XML', 400);
        }
    }

    public function importXmlData($item, $bookId, $previousIndex = null)
    {
        $validator = Validator::make($item['@attributes'], [
            'titulo' => ['required', 'string'],
            'pagina' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $bookIndex = new BookIndex();
        $bookIndex->book_id = $bookId;
        $bookIndex->index_id = $previousIndex;
        $bookIndex->title = $item['@attributes']['titulo'];
        $bookIndex->page = $item['@attributes']['pagina'];
        $bookIndex->save();

        if (isset($item['item']) && count($item['item'])) {
            collect($item['item'])->each(function($itemIndex) use ($bookId, $previousIndex, $bookIndex) {
                $this->importXmlData($itemIndex, $bookId, $bookIndex->id);
            });
        }
    }

}
