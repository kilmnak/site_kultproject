<?php
// includes/venue_templates.php - Шаблоны рассадки для различных типов заведений

class VenueTemplates {
    
    /**
     * Получить все доступные шаблоны
     */
    public static function getTemplates() {
        return [
            'club' => [
                'name' => 'Клуб',
                'description' => 'Клуб с VIP зоной сверху, танцполом снизу и вторым этажом по краям',
                'zones' => [
                    'vip' => [
                        'name' => 'VIP',
                        'description' => 'Премиум зона сверху по центру',
                        'capacity' => 80,
                        'price_multiplier' => 2.5
                    ],
                    'dance_floor' => [
                        'name' => 'Танцпол',
                        'description' => 'Основная танцевальная зона снизу по центру',
                        'capacity' => 150,
                        'price_multiplier' => 1.0
                    ],
                    'second_floor' => [
                        'name' => 'Второй этаж',
                        'description' => 'Балконы по краям зала',
                        'capacity' => 120,
                        'price_multiplier' => 1.8
                    ]
                ]
            ],
            'cinema' => [
                'name' => 'Кинотеатр',
                'description' => 'Классический кинотеатр с рядами кресел',
                'zones' => [
                    'parquet' => [
                        'name' => 'Партер',
                        'description' => 'Основные места перед экраном',
                        'capacity' => 150,
                        'price_multiplier' => 1.2
                    ],
                    'balcony' => [
                        'name' => 'Балкон',
                        'description' => 'Места на балконе',
                        'capacity' => 50,
                        'price_multiplier' => 1.0
                    ]
                ]
            ],
            'theater' => [
                'name' => 'Театр',
                'description' => 'Театральный зал с партером и балконами',
                'zones' => [
                    'orchestra' => [
                        'name' => 'Партер',
                        'description' => 'Места в партере',
                        'capacity' => 200,
                        'price_multiplier' => 1.5
                    ],
                    'mezzanine' => [
                        'name' => 'Бельэтаж',
                        'description' => 'Места в бельэтаже',
                        'capacity' => 100,
                        'price_multiplier' => 1.2
                    ],
                    'balcony' => [
                        'name' => 'Балкон',
                        'description' => 'Места на балконе',
                        'capacity' => 80,
                        'price_multiplier' => 1.0
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Создать места по шаблону клуба
     */
    public static function createClubLayout($eventId, $basePrice, $db) {
        $seats = [];
        
        // VIP зона - премиум места сверху по центру (80 мест)
        for ($i = 1; $i <= 80; $i++) {
            $seats[] = [
                'event_id' => $eventId,
                'seat_number' => "VIP{$i}",
                'row_number' => 'VIP',
                'section' => 'VIP',
                'price' => $basePrice * 2.5,
                'status' => 'available'
            ];
        }
        
        // Танцпол - основная зона снизу по центру (150 мест)
        for ($i = 1; $i <= 150; $i++) {
            $seats[] = [
                'event_id' => $eventId,
                'seat_number' => "DF{$i}",
                'row_number' => 'Танцпол',
                'section' => 'Танцпол',
                'price' => $basePrice * 1.0,
                'status' => 'available'
            ];
        }
        
        // Второй этаж - балконы по краям (120 мест)
        for ($i = 1; $i <= 120; $i++) {
            $seats[] = [
                'event_id' => $eventId,
                'seat_number' => "SF{$i}",
                'row_number' => '2-й этаж',
                'section' => 'Второй этаж',
                'price' => $basePrice * 1.8,
                'status' => 'available'
            ];
        }
        
        return $seats;
    }
    
    /**
     * Создать места по шаблону кинотеатра
     */
    public static function createCinemaLayout($eventId, $basePrice, $db) {
        $seats = [];
        
        // Партер - 15 рядов по 10 мест
        for ($row = 1; $row <= 15; $row++) {
            for ($seat = 1; $seat <= 10; $seat++) {
                $seats[] = [
                    'event_id' => $eventId,
                    'seat_number' => "P{$row}-{$seat}",
                    'row_number' => $row,
                    'section' => 'Партер',
                    'price' => $basePrice * 1.2,
                    'status' => 'available'
                ];
            }
        }
        
        // Балкон - 5 рядов по 10 мест
        for ($row = 1; $row <= 5; $row++) {
            for ($seat = 1; $seat <= 10; $seat++) {
                $seats[] = [
                    'event_id' => $eventId,
                    'seat_number' => "B{$row}-{$seat}",
                    'row_number' => "B{$row}",
                    'section' => 'Балкон',
                    'price' => $basePrice * 1.0,
                    'status' => 'available'
                ];
            }
        }
        
        return $seats;
    }
    
    /**
     * Создать места по шаблону театра
     */
    public static function createTheaterLayout($eventId, $basePrice, $db) {
        $seats = [];
        
        // Партер - 20 рядов по 10 мест
        for ($row = 1; $row <= 20; $row++) {
            for ($seat = 1; $seat <= 10; $seat++) {
                $seats[] = [
                    'event_id' => $eventId,
                    'seat_number' => "O{$row}-{$seat}",
                    'row_number' => $row,
                    'section' => 'Партер',
                    'price' => $basePrice * 1.5,
                    'status' => 'available'
                ];
            }
        }
        
        // Бельэтаж - 10 рядов по 10 мест
        for ($row = 1; $row <= 10; $row++) {
            for ($seat = 1; $seat <= 10; $seat++) {
                $seats[] = [
                    'event_id' => $eventId,
                    'seat_number' => "M{$row}-{$seat}",
                    'row_number' => "M{$row}",
                    'section' => 'Бельэтаж',
                    'price' => $basePrice * 1.2,
                    'status' => 'available'
                ];
            }
        }
        
        // Балкон - 8 рядов по 10 мест
        for ($row = 1; $row <= 8; $row++) {
            for ($seat = 1; $seat <= 10; $seat++) {
                $seats[] = [
                    'event_id' => $eventId,
                    'seat_number' => "BA{$row}-{$seat}",
                    'row_number' => "BA{$row}",
                    'section' => 'Балкон',
                    'price' => $basePrice * 1.0,
                    'status' => 'available'
                ];
            }
        }
        
        return $seats;
    }
    
    /**
     * Создать места по выбранному шаблону
     */
    public static function createLayoutByTemplate($templateType, $eventId, $basePrice, $db) {
        switch ($templateType) {
            case 'club':
                return self::createClubLayout($eventId, $basePrice, $db);
            case 'cinema':
                return self::createCinemaLayout($eventId, $basePrice, $db);
            case 'theater':
                return self::createTheaterLayout($eventId, $basePrice, $db);
            default:
                return [];
        }
    }
    
    /**
     * Получить HTML для отображения схемы рассадки
     */
    public static function getLayoutHTML($templateType, $seats = []) {
        switch ($templateType) {
            case 'club':
                return self::getClubLayoutHTML($seats);
            case 'cinema':
                return self::getCinemaLayoutHTML($seats);
            case 'theater':
                return self::getTheaterLayoutHTML($seats);
            default:
                return '<div class="alert alert-info">Схема рассадки не настроена</div>';
        }
    }
    
    /**
     * HTML для схемы клуба
     */
    private static function getClubLayoutHTML($seats) {
        $html = '<div class="club-layout">';
        
        // Сцена
        $html .= '<div class="stage-area mb-4">';
        $html .= '<div class="stage">СЦЕНА</div>';
        $html .= '</div>';
        
        // VIP зона (сверху по центру)
        $html .= '<div class="vip-zone mb-3">';
        $html .= '<h6 class="zone-title">VIP зона (сверху по центру)</h6>';
        $html .= '<div class="seats-grid vip-grid">';
        
        $vipSeats = array_filter($seats, function($seat) {
            return $seat['section'] === 'VIP';
        });
        
        foreach ($vipSeats as $seat) {
            $statusClass = self::getSeatStatusClass($seat['status']);
            $disabled = $seat['status'] !== 'available' ? 'disabled' : '';
            $price = $seat['price'] ?? 0;
            $category = htmlspecialchars($seat['category_name'] ?? 'Стандарт');
            $html .= '<label class="seat vip-seat ' . $statusClass . '" data-price="' . $price . '" data-category="' . $category . '">';
            $html .= '<input type="checkbox" name="seat_ids[]" value="' . $seat['id'] . '" ' . $disabled . ' class="seat-checkbox d-none">';
            $html .= '<span class="seat-number">' . $seat['seat_number'] . '</span>';
            $html .= '<small class="seat-price">' . number_format($price) . ' ₽</small>';
            $html .= '</label>';
        }
        
        $html .= '</div></div>';
        
        // Танцпол (снизу по центру)
        $html .= '<div class="dance-floor-zone mb-3">';
        $html .= '<h6 class="zone-title">Танцпол (снизу по центру)</h6>';
        $html .= '<div class="seats-grid dance-grid">';
        
        $danceSeats = array_filter($seats, function($seat) {
            return $seat['section'] === 'Танцпол';
        });
        
        foreach ($danceSeats as $seat) {
            $statusClass = self::getSeatStatusClass($seat['status']);
            $disabled = $seat['status'] !== 'available' ? 'disabled' : '';
            $price = $seat['price'] ?? 0;
            $category = htmlspecialchars($seat['category_name'] ?? 'Стандарт');
            $html .= '<label class="seat dance-seat ' . $statusClass . '" data-price="' . $price . '" data-category="' . $category . '">';
            $html .= '<input type="checkbox" name="seat_ids[]" value="' . $seat['id'] . '" ' . $disabled . ' class="seat-checkbox d-none">';
            $html .= '<span class="seat-number">' . $seat['seat_number'] . '</span>';
            $html .= '<small class="seat-price">' . number_format($price) . ' ₽</small>';
            $html .= '</label>';
        }
        
        $html .= '</div></div>';
        
        // Второй этаж (по краям)
        $html .= '<div class="second-floor-zone">';
        $html .= '<h6 class="zone-title">Второй этаж (по краям)</h6>';
        $html .= '<div class="seats-grid second-floor-grid">';
        
        $secondFloorSeats = array_filter($seats, function($seat) {
            return $seat['section'] === 'Второй этаж';
        });
        
        foreach ($secondFloorSeats as $seat) {
            $statusClass = self::getSeatStatusClass($seat['status']);
            $disabled = $seat['status'] !== 'available' ? 'disabled' : '';
            $price = $seat['price'] ?? 0;
            $category = htmlspecialchars($seat['category_name'] ?? 'Стандарт');
            $html .= '<label class="seat second-floor-seat ' . $statusClass . '" data-price="' . $price . '" data-category="' . $category . '">';
            $html .= '<input type="checkbox" name="seat_ids[]" value="' . $seat['id'] . '" ' . $disabled . ' class="seat-checkbox d-none">';
            $html .= '<span class="seat-number">' . $seat['seat_number'] . '</span>';
            $html .= '<small class="seat-price">' . number_format($price) . ' ₽</small>';
            $html .= '</label>';
        }
        
        $html .= '</div></div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * HTML для схемы кинотеатра
     */
    private static function getCinemaLayoutHTML($seats) {
        $html = '<div class="cinema-layout">';
        
        // Экран
        $html .= '<div class="screen-area mb-4">';
        $html .= '<div class="screen">ЭКРАН</div>';
        $html .= '</div>';
        
        // Места кинотеатра
        $html .= '<div class="cinema-seats">';
        
        $seatsByRow = [];
        foreach ($seats as $seat) {
            $seatsByRow[$seat['row_number']][] = $seat;
        }
        
        foreach ($seatsByRow as $rowNumber => $rowSeats) {
            $html .= '<div class="cinema-row mb-2">';
            $html .= '<div class="row-label"><strong>Ряд ' . $rowNumber . '</strong></div>';
            $html .= '<div class="seats-container">';
            
            foreach ($rowSeats as $seat) {
                $statusClass = self::getSeatStatusClass($seat['status']);
                $disabled = $seat['status'] !== 'available' ? 'disabled' : '';
                $price = $seat['price'] ?? 0;
                $category = htmlspecialchars($seat['category_name'] ?? 'Стандарт');
                $html .= '<label class="seat cinema-seat ' . $statusClass . '" data-price="' . $price . '" data-category="' . $category . '">';
                $html .= '<input type="checkbox" name="seat_ids[]" value="' . $seat['id'] . '" ' . $disabled . ' class="seat-checkbox d-none">';
                $html .= '<span class="seat-number">' . $seat['seat_number'] . '</span>';
                $html .= '</label>';
            }
            
            $html .= '</div></div>';
        }
        
        $html .= '</div></div>';
        
        return $html;
    }
    
    /**
     * HTML для схемы театра
     */
    private static function getTheaterLayoutHTML($seats) {
        $html = '<div class="theater-layout">';
        
        // Сцена
        $html .= '<div class="stage-area mb-4">';
        $html .= '<div class="stage">СЦЕНА</div>';
        $html .= '</div>';
        
        // Места театра
        $html .= '<div class="theater-seats">';
        
        $seatsByRow = [];
        foreach ($seats as $seat) {
            $seatsByRow[$seat['row_number']][] = $seat;
        }
        
        foreach ($seatsByRow as $rowNumber => $rowSeats) {
            $html .= '<div class="theater-row mb-2">';
            $html .= '<div class="row-label"><strong>Ряд ' . $rowNumber . '</strong></div>';
            $html .= '<div class="seats-container">';
            
            foreach ($rowSeats as $seat) {
                $statusClass = self::getSeatStatusClass($seat['status']);
                $disabled = $seat['status'] !== 'available' ? 'disabled' : '';
                $price = $seat['price'] ?? 0;
                $category = htmlspecialchars($seat['category_name'] ?? 'Стандарт');
                $html .= '<label class="seat theater-seat ' . $statusClass . '" data-price="' . $price . '" data-category="' . $category . '">';
                $html .= '<input type="checkbox" name="seat_ids[]" value="' . $seat['id'] . '" ' . $disabled . ' class="seat-checkbox d-none">';
                $html .= '<span class="seat-number">' . $seat['seat_number'] . '</span>';
                $html .= '</label>';
            }
            
            $html .= '</div></div>';
        }
        
        $html .= '</div></div>';
        
        return $html;
    }
    
    /**
     * Получить CSS класс для статуса места
     */
    private static function getSeatStatusClass($status) {
        switch ($status) {
            case 'available':
                return 'seat-available';
            case 'booked':
                return 'seat-booked';
            case 'sold':
                return 'seat-sold';
            case 'blocked':
                return 'seat-blocked';
            default:
                return 'seat-blocked';
        }
    }
}
?>

<style>
/* Стили для схем рассадки */
.cinema-layout, .theater-layout {
    max-width: 100%;
    margin: 0 auto;
}

.screen-area, .stage-area {
    text-align: center;
    margin-bottom: 20px;
}

.screen, .stage {
    background: #343a40;
    color: white;
    padding: 15px 30px;
    border-radius: 8px;
    font-weight: bold;
    font-size: 18px;
    display: inline-block;
    min-width: 200px;
}

.cinema-seats, .theater-seats {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.cinema-row, .theater-row {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 8px 0;
    width: 100%;
    min-width: 0;
}

.row-label {
    min-width: 80px;
    font-size: 14px;
    color: #495057;
    text-align: right;
    padding-right: 10px;
}

.seats-container {
    display: flex;
    gap: 2px;
    flex-wrap: nowrap;
    align-items: center;
    justify-content: flex-start;
    width: 100%;
    max-width: 100%;
}

.seat {
    width: 28px;
    height: 28px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    flex-shrink: 0;
    margin: 1px;
    padding: 0;
    text-align: center;
}

.seat input {
    display: none;
}

.seat:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.seat-available {
    background: #28a745;
    color: white;
}

.seat-available:hover {
    background: #218838;
    border-color: #1e7e34;
}

.seat-booked {
    background: #ffc107;
    color: #212529;
}

.seat-booked:hover {
    background: #e0a800;
    border-color: #d39e00;
}

.seat-sold {
    background: #dc3545;
    color: white;
}

.seat-sold:hover {
    background: #c82333;
    border-color: #bd2130;
}

.seat-blocked {
    background: #6c757d;
    color: white;
    cursor: not-allowed;
}

.seat-blocked:hover {
    transform: none;
    box-shadow: none;
}

.seat-selected {
    transform: scale(1.1) !important;
    box-shadow: 0 0 20px rgba(0, 123, 255, 0.8) !important;
    border-color: #007bff !important;
    background: #007bff !important;
    color: white !important;
}

.seat-number {
    font-size: 8px;
    line-height: 1;
}

/* Адаптивность */
@media (max-width: 768px) {
    .cinema-row, .theater-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .row-label {
        text-align: left;
        min-width: auto;
        padding-right: 0;
    }
    
    .seats-container {
        width: 100%;
        justify-content: flex-start;
        gap: 1px;
    }
    
    .seat {
        width: 24px;
        height: 24px;
        font-size: 8px;
    }
    
    .seat-number {
        font-size: 7px;
    }
    
    .screen, .stage {
        padding: 10px 20px;
        font-size: 16px;
        min-width: 150px;
    }
}

@media (max-width: 480px) {
    .seat {
        width: 20px;
        height: 20px;
        font-size: 7px;
    }
    
    .seat-number {
        font-size: 6px;
    }
    
    .seats-container {
        gap: 1px;
    }
}
</style>
