<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Thực thể Đầu sách (Book Title Entity Model)
 * Tuân thủ Rule 8, 9 (AGENTS.md)
 */
class Book
{
    /**
     * @param list<string> $authors
     * @param list<array<string, mixed>> $copies
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $isbn = null,
        public readonly ?string $description = null,
        public readonly ?int $publishYear = null,
        public readonly ?int $edition = null,
        public readonly ?string $language = 'Tiếng Việt',
        public readonly int $totalQuantity = 0,
        public readonly int $availableQuantity = 0,
        public readonly string $authorNames = '',
        public readonly array $authors = [],
        public readonly array $copies = [],
        public readonly ?string $location = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        $authorStr = (string)($data['tac_gia'] ?? $data['authorNames'] ?? '');
        $authorsList = !empty($data['authors']) && is_array($data['authors'])
            ? $data['authors']
            : ($authorStr !== '' ? array_map('trim', explode(',', $authorStr)) : []);

        $copiesList = !empty($data['copies']) && is_array($data['copies'])
            ? $data['copies']
            : [];

        $totalCopies = (int)($data['tong_cuon'] ?? $data['so_luong'] ?? count($copiesList));
        $availableCopies = (int)($data['co_the_muon'] ?? $data['availableQuantity'] ?? $totalCopies);

        return new self(
            id: (string)($data['id_dau_sach'] ?? $data['id'] ?? ''),
            title: (string)($data['tieu_de'] ?? $data['title'] ?? ''),
            isbn: isset($data['isbn']) && $data['isbn'] !== '' ? (string)$data['isbn'] : null,
            description: isset($data['mo_ta']) && $data['mo_ta'] !== '' ? (string)$data['mo_ta'] : null,
            publishYear: isset($data['nam_xuat_ban']) && $data['nam_xuat_ban'] !== '' ? (int)$data['nam_xuat_ban'] : null,
            edition: isset($data['lan_xuat_ban']) && $data['lan_xuat_ban'] !== '' ? (int)$data['lan_xuat_ban'] : null,
            language: isset($data['ngon_ngu']) && $data['ngon_ngu'] !== '' ? (string)$data['ngon_ngu'] : 'Tiếng Việt',
            totalQuantity: $totalCopies,
            availableQuantity: $availableCopies,
            authorNames: $authorStr,
            authors: $authorsList,
            copies: $copiesList,
            location: !empty($data['location']) ? (string)$data['location'] : (!empty($data['vi_tri']) ? (string)$data['vi_tri'] : null),
            createdAt: isset($data['ngay_tao']) ? (string)$data['ngay_tao'] : null,
            updatedAt: isset($data['ngay_cap_nhat']) ? (string)$data['ngay_cap_nhat'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'description' => $this->description,
            'publishYear' => $this->publishYear,
            'edition' => $this->edition,
            'language' => $this->language,
            'totalQuantity' => $this->totalQuantity,
            'availableQuantity' => $this->availableQuantity,
            'authorNames' => $this->authorNames,
            'authors' => $this->authors,
            'copies' => $this->copies,
            'location' => $this->location,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
