<?php

namespace App\Services;

class MockDataService
{
    public function getDashboardStats(): array
    {
        return [
            'today_sales' => 2720,
            'today_clients' => 24,
            'monthly_sales' => 124500,
            'last_sync' => 'อัปเดตล่าสุด 10:42',
            'top_services' => [
                ['name' => 'นวดแผนไทย', 'price' => 500, 'count' => 15, 'icon' => '💆', 'percent' => 38],
                ['name' => 'นวดอโรมา', 'price' => 800, 'count' => 8, 'icon' => '🌿', 'percent' => 29],
                ['name' => 'นวดฝ่าเท้า', 'price' => 350, 'count' => 6, 'icon' => '🦶', 'percent' => 18],
                ['name' => 'สครับผิว', 'price' => 1200, 'count' => 3, 'icon' => '🧴', 'percent' => 15],
            ],
            'top_masseuses' => [
                ['id' => 'MS001', 'name' => 'ฟ้า', 'amount' => 5200, 'queue_count' => 6, 'avatar' => 'https://i.pravatar.cc/150?u=fa'],
                ['id' => 'MS002', 'name' => 'ปราง', 'amount' => 4800, 'queue_count' => 5, 'avatar' => 'https://i.pravatar.cc/150?u=prang'],
                ['id' => 'MS003', 'name' => 'แก้ว', 'amount' => 3500, 'queue_count' => 4, 'avatar' => 'https://i.pravatar.cc/150?u=kaew'],
            ],
        ];
    }

    public function getPosItems(): array
    {
        return [
            ['id' => 'SVC001', 'type' => 'service', 'name' => 'นวดแผนไทย (Thai)', 'price' => 500, 'duration' => 60, 'color' => '#31b8e9'],
            ['id' => 'SVC002', 'type' => 'service', 'name' => 'นวดน้ำมันอโรมา', 'price' => 800, 'duration' => 60, 'color' => '#18c4b3'],
            ['id' => 'SVC003', 'type' => 'service', 'name' => 'สครับผิว (Scrub)', 'price' => 1200, 'duration' => 90, 'color' => '#5fc5ff'],
            ['id' => 'SVC004', 'type' => 'service', 'name' => 'นวดศีรษะ บ่า ไหล่', 'price' => 400, 'duration' => 30, 'color' => '#22d3ee'],
            ['id' => 'SVC005', 'type' => 'service', 'name' => 'นวดฝ่าเท้า (Foot)', 'price' => 350, 'duration' => 45, 'color' => '#14b8a6'],
            ['id' => 'PRD001', 'type' => 'product', 'name' => 'น้ำมันนวดอโรมา 100ml', 'price' => 390, 'duration' => null, 'color' => '#4dd4bf'],
            ['id' => 'PRD002', 'type' => 'product', 'name' => 'ลูกประคบสมุนไพร', 'price' => 180, 'duration' => null, 'color' => '#3fbeb2'],
            ['id' => 'PRD003', 'type' => 'product', 'name' => 'สบู่สมุนไพร', 'price' => 150, 'duration' => null, 'color' => '#5ecfe0'],
        ];
    }

    public function getCustomers(): array
    {
        return [
            ['id' => 'CUS-WALKIN', 'name' => 'Walk-in', 'phone' => '-', 'line_id' => '-', 'points' => 0],
            ['id' => 'CUS001', 'name' => 'คุณสมชาย ใจดี', 'phone' => '089-111-1111', 'line_id' => '@somchai', 'points' => 150],
            ['id' => 'CUS002', 'name' => 'คุณวิภาวรรณ', 'phone' => '081-222-2222', 'line_id' => '@wipa', 'points' => 420],
            ['id' => 'CUS003', 'name' => 'คุณธนา', 'phone' => '095-333-3333', 'line_id' => '@thana', 'points' => 80],
        ];
    }

    public function getRooms(): array
    {
        return [
            ['id' => 'R1', 'name' => 'ห้องนวด 1 / เตียง A'],
            ['id' => 'R2', 'name' => 'ห้องนวด 1 / เตียง B'],
            ['id' => 'R3', 'name' => 'ห้องสปา 2'],
        ];
    }

    public function getStaff(): array
    {
        return [
            [
                'id' => 'MS001',
                'name' => 'ฟ้า',
                'role' => 'Therapist (Senior)',
                'status' => 'กำลังให้บริการ',
                'income' => 5200,
                'commission' => 1560,
                'avatar' => 'https://i.pravatar.cc/150?u=fa',
                'shift' => '10:00 - 19:00',
                'queue' => [
                    ['booking_id' => 'BK001', 'customer' => 'คุณสมชาย', 'service' => 'นวดแผนไทย', 'start' => '10:00', 'end' => '11:00', 'room_id' => 'R1', 'status' => 'in_service', 'color' => '#31b8e9'],
                    ['booking_id' => 'BK004', 'customer' => 'คุณวิภาวรรณ', 'service' => 'นวดอโรมา', 'start' => '13:00', 'end' => '14:00', 'room_id' => 'R1', 'status' => 'waiting', 'color' => '#18c4b3'],
                ],
            ],
            [
                'id' => 'MS002',
                'name' => 'ปราง',
                'role' => 'Therapist (Junior)',
                'status' => 'เข้างานแล้ว',
                'income' => 4800,
                'commission' => 1200,
                'avatar' => 'https://i.pravatar.cc/150?u=prang',
                'shift' => '11:00 - 20:00',
                'queue' => [
                    ['booking_id' => 'BK002', 'customer' => 'คุณธนา', 'service' => 'นวดฝ่าเท้า', 'start' => '11:30', 'end' => '12:15', 'room_id' => 'R2', 'status' => 'completed', 'color' => '#5fc5ff'],
                ],
            ],
            [
                'id' => 'MS003',
                'name' => 'แก้ว',
                'role' => 'Therapist',
                'status' => 'พักเบรก',
                'income' => 3500,
                'commission' => 875,
                'avatar' => 'https://i.pravatar.cc/150?u=kaew',
                'shift' => '10:00 - 18:00',
                'queue' => [],
            ],
        ];
    }

    public function getQueueStatuses(): array
    {
        return [
            'waiting' => 'รอรับบริการ',
            'in_service' => 'กำลังนวด',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก',
        ];
    }
}
