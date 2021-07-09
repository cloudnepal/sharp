<?php

namespace Code16\Sharp\Form\Fields\Formatters;

use Code16\Sharp\Form\Eloquent\Uploads\SharpUploadModel;
use Code16\Sharp\Form\Fields\SharpFormField;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MarkdownFormatter extends SharpFieldFormatter
{

    /**
     * @param SharpFormField $field
     * @param $value
     * @return mixed
     */
    function toFront(SharpFormField $field, $value)
    {
        return [
            "text" => $value,
            "files" => $this->extractEmbeddedUploads($value)
                ->map(function(array $uploadAttributes) {
                    return $this->getUpload($uploadAttributes);
                })
                ->toArray()
        ];
    }

    /**
     * @param SharpFormField $field
     * @param string $attribute
     * @param $value
     * @return mixed
     */
    function fromFront(SharpFormField $field, string $attribute, $value)
    {
        $text = $value['text'] ?? '';

        if(count($value["files"] ?? [])) {
            $dom = $this->getDomDocument($text);
            $uploadFormatter = app(UploadFormatter::class);

            foreach($value["files"] as $file) {
                $upload = $uploadFormatter
                    ->setInstanceId($this->instanceId)
                    ->fromFront($field, $attribute, $file);

                if(isset($upload["file_name"])) {
                    // New file was uploaded. We have to update the name of the file in the markdown
                    
                    /** @var DOMElement $domElement */
                    $domElement = collect($dom->getElementsByTagName('x-sharp-media'))
                        ->first(function(DOMElement $uploadElement) use ($file) {
                            return $uploadElement->getAttribute("name") === $file["name"];
                        });
                    
                    if($domElement) {
                        $domElement->setAttribute("name", basename($upload["file_name"]));
                        $domElement->setAttribute("path", $upload["file_name"]);
                        $domElement->setAttribute("disk", $upload["disk"]);
                    }
                }
            }
            
            $text = $this->formatDomStringValue($dom);
        }
        
        // Normalize \n
        return preg_replace('/\R/', "\n", $text);
    }
    
    protected function getDomDocument(string $content): DOMDocument
    {
        return tap(new DOMDocument(), function(DOMDocument $dom) use ($content) {
            @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        });
    }

    protected function extractEmbeddedUploads($contents = null): Collection
    {
        if(!$contents) {
            return collect();
        }
        
        return collect($contents)
            // Generalize to a collection of contents to handle both regular case (where contents is a string)
            // and localized case (where contents in an array of string)
            ->map(function($content) {
                return collect($this->getDomDocument($content)->getElementsByTagName('x-sharp-media'))
                    ->map(function(DOMElement $uploadElement) {
                        $hasFilterAttr = false;
                        $uploadAttributes = collect($uploadElement->attributes)
                            ->filter(function($attr) use (&$hasFilterAttr) {
                                if(Str::startsWith($attr->nodeName, "filter-")) {
                                    $hasFilterAttr = true;
                                    return false;
                                }
                                return true;
                            })
                            ->mapWithKeys(function($attr) {
                                return [$attr->nodeName => $attr->nodeValue];
                            })
                            ->toArray();
                        
                        if($hasFilterAttr) {
                            if($cropData = $uploadElement->attributes->getNamedItem("filter-crop")) {
                                $cropValues = explode(",", $cropData->nodeValue);
                                $uploadAttributes["filters"]["crop"] = [
                                    "x" => $cropValues[0],
                                    "y" => $cropValues[1],
                                    "width" => $cropValues[2],
                                    "height" => $cropValues[3],
                                ];
                            }
                            if($rotateAngle = $uploadElement->attributes->getNamedItem("filter-rotate")) {
                                $uploadAttributes["filters"]["rotate"]["angle"] = $rotateAngle->nodeValue;
                            }
                        }
                        
                        return $uploadAttributes;
                    });
            })
            ->flatten(1);
    }

    protected function formatDomStringValue(DOMDocument $dom): string
    {
        $wrapperElement = $dom->firstChild;
        $newParent = $wrapperElement->parentNode;
        foreach ($wrapperElement->childNodes as $child) {
            $newParent->insertBefore(
                $child->cloneNode(true), 
                $wrapperElement
            );
        }
        $newParent->removeChild($wrapperElement);
        
        return trim($dom->saveHTML());
    }

    protected function getUpload(array $uploadAttributes): array
    {
        $model = new SharpUploadModel([
            "file_name" => $uploadAttributes["path"],
            "disk" => $uploadAttributes["disk"],
            "filters" => $uploadAttributes["filters"] ?? null
        ]);

        return array_merge(
            $uploadAttributes,
            [
                "size" => $this->getFileSize($uploadAttributes),
                "thumbnail" => $model->thumbnail(200, 200)
            ]
        );
    }

    protected function deleteThumbnails(array $uploadAttributes): void
    {
        $uploadModel = new SharpUploadModel(
            [
                "file_name" => $uploadAttributes["path"],
                "disk" => $uploadAttributes["disk"]
            ]
        );

        $uploadModel->deleteAllThumbnails();
    }

    protected function getFileSize(array $uploadAttributes): ?int
    {
        try {
            return Storage::disk($uploadAttributes["disk"])
                ->size($uploadAttributes["path"]);

        } catch(\Exception $ex) {
            return null;
        }
    }
}
