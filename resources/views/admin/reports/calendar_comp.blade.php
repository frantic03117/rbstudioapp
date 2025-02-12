<section>
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="w-100">
                    <form action="" method="get" class="row">
                        <div class="col-md-3">
                            <label for="">Select Vendor</label>
                            <select name="vendor_id" onchange="getStudiosList(event)" id="vendor_id" class="form-select">
                                <option value="">All</option>
                                @foreach ($vendors as $v)
                                    <option value="{{ $v->id }}" @selected($vid == $v->id)>{{ $v->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="">Select Studio</label>
                            <select name="studio_id" id="studio_id" onchange="getServiceByStudio(event)"
                                class="form-select">
                                <option value="">All</option>
                                @foreach ($studios as $s)
                                    <option value="{{ $s->id }}" @selected($sid == $s->id)>{{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="">Select Service</label>
                            <select class="form-select" name="service_id" id="service_id">
                                <option value="">All</option>
                                @foreach ($services as $sv)
                                    <option value="{{ $sv->id }}" @selected($sv->id == $service_id)>{{ $sv->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="" class="d-block">&nbsp;</label>
                            <button class="btn w-100 btn-gradient">Submit</button>
                        </div>
                        <script>
                            const getStudiosList = (e) => {
                                let vid = e.target.value;
                                $.post("{{ route('ajax_studios') }}", {
                                    vendor_id: vid
                                }, function(res) {
                                    $("#studio_id").html(res)
                                })
                            };
                            const getServiceByStudio = (e) => {
                                let vid = e.target.value;
                                $.post("{{ route('ajax_services') }}", {
                                    studio_id: vid
                                }, function(res) {
                                    $("#service_id").html(res)
                                })
                            };
                        </script>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">

            <div class="col-md-12">
                {{ $defaultView }}
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</section>
<script>
    const evurl =
        "{{ route('events') }}?studio_id={{ $sid }}&vendor_id={{ $vid }}&service_id={{ $service_id }}";
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            timeZone: 'local',
            headerToolbar: {
                left: 'prev,next,today,myCustomButton',
                center: 'title',
                right: 'dayGridMonth,timeGridDay,listWeek,timeGridWeek'
            },
            initialView: "{{ $defaultView }}",
            displayEventTime: true,
            selectable: true,
            titleFormat: {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            },
            dayHeaderFormat: {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
            },
            customButtons: {
                myCustomButton: {
                    text: 'Add New Booking',
                    className: "btn btn-gradient",
                    click: function() {
                        window.location.href = "{{ route('booking.create') }}"
                    }
                }
            },
            allDaySlot: true,
            eventClassNames: "cursor",
            events: evurl,
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            eventClick: function(info) {
                const id = info.event?._instance?.instanceId;

                if (!id) return; // Safety check in case instanceId is undefined

                const eventDate = new Date(info.event.start); // Use `info.event.start` directly
                const today = new Date();

                // Remove time part for accurate comparison
                eventDate.setHours(0, 0, 0, 0);
                today.setHours(0, 0, 0, 0);

                let slg = "";
                if (eventDate.getTime() === today.getTime()) {
                    slg = "today";
                } else if (eventDate.getTime() > today.getTime()) {
                    slg = "upcoming";
                } else {
                    slg = "past";
                }

                // Construct the correct route URL dynamically
                const routeBase = "{{ route('bookingsview', ['slug' => '__SLUG__']) }}";
                const finalUrl = routeBase.replace('__SLUG__', slg) + "?booking_id=" + id;

                window.location.href = finalUrl;
            }

        });
        calendar.render();
    });
</script>
