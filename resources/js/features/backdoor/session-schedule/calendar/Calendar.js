import { Calendar } from "fullcalendar";
import themePlugin from "fullcalendar/themes/classic";
import dayGridPlugin from "fullcalendar/daygrid";
import timeGridPlugin from "fullcalendar/timegrid";
import listPlugin from "fullcalendar/list";
import interactionPlugin from "fullcalendar/interaction";
import idLocale from "fullcalendar/locales/id";
import route from "@/lib/route";

import "fullcalendar/skeleton.css";
import "fullcalendar/themes/classic/theme.css";
import "fullcalendar/themes/classic/palette.css";

export default function CalendarPage() {

  let calendarInstance = null;

  const calendarSession = (refs) => {
    calendarInstance = new Calendar(refs, {
      plugins: [themePlugin, dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
      locale: idLocale,
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay,listMonth",
      },
      initialView: "dayGridMonth",
      views: {
        dayGridMonth: {
          dayMaxEvents: 0,
        },
        timeGridWeek: {
          dayMaxEvents: true,
          dayHeaderFormat: { weekday: "long", day: "numeric" },
        },
        timeGridDay: {
          dayMaxEvents: true,
        },
        listMonth: {
          listDayFormat: {
            weekday: "long",
            day: "numeric",
            month: "long",
          },
        },
      },
      selectable: false,

      // mengubah "+N more" menjadi "N Jadwal"
      moreLinkContent: (args) => `${args.num} Jadwal`,

      events: route("backdoor.session-schedule.calendar.events"),

      eventClick: (info) => {
        if (info.event.url) {
          info.jsEvent.preventDefault();
          window.location.href = info.event.url;
        }
      },

      height: "auto",
      navLinks: true,
      nowIndicator: true,
    });
    calendarInstance.render();
  };

  return {
    calendarSession,
  };
}
